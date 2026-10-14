<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

/**
 * Книга каталога. Может иметь несколько авторов.
 *
 * @property int $id
 * @property string $title
 * @property int $issue_year
 * @property string|null $description
 * @property string $isbn
 * @property string|null $cover_path
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Author[] $authors
 * @property-read string|null $coverUrl
 * @property-read string $authorsList
 */
class Book extends ActiveRecord
{
    /**
     * Каталог обложек относительно @webroot.
     */
    const COVER_DIR = 'uploads/covers';

    /**
     * Нижняя граница списка годов выпуска.
     */
    const YEAR_MIN = 1800;

    /**
     * @var int[] идентификаторы авторов из формы
     */
    public $authorIds = [];

    /**
     * @var UploadedFile|null
     */
    public $coverFile;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%book}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $years = array_keys(static::getYearOptions());

        return [
            [['title', 'issue_year', 'isbn', 'authorIds'], 'required'],
            [['title'], 'trim'],
            [['title'], 'string', 'max' => 255],
            [['description'], 'string'],
            [['issue_year'], 'integer'],
            [['issue_year'], 'in', 'range' => $years, 'message' => 'Выберите год из списка.'],
            [['isbn'], 'trim'],
            [['isbn'], 'string', 'max' => 32],
            [['isbn'], 'match', 'pattern' => '/^[0-9Xx\-]{10,17}$/', 'message' => 'Укажите ISBN из 10 или 13 символов.'],
            [['isbn'], 'unique'],
            [['authorIds'], 'each', 'rule' => ['integer']],
            [
                ['authorIds'],
                'each',
                'rule' => ['exist', 'targetClass' => Author::class, 'targetAttribute' => 'id'],
            ],
            [
                ['coverFile'],
                'file',
                'extensions' => 'png, jpg, jpeg, gif, webp',
                'maxSize' => 5 * 1024 * 1024,
                'skipOnEmpty' => true,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название',
            'issue_year' => 'Год выпуска',
            'description' => 'Описание',
            'isbn' => 'ISBN',
            'cover_path' => 'Обложка',
            'coverFile' => 'Фото главной страницы',
            'authorIds' => 'Авторы',
            'created_at' => 'Создана',
            'updated_at' => 'Обновлена',
        ];
    }

    /**
     * Авторы книги.
     *
     * @return ActiveQuery
     */
    public function getAuthors()
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('{{%book_author}}', ['book_id' => 'id']);
    }

    /**
     * Список годов для выпадающего списка формы книги.
     *
     * @return array<int, string>
     */
    public static function getYearOptions()
    {
        $maxYear = (int) date('Y') + 1;
        $years = [];
        for ($year = $maxYear; $year >= self::YEAR_MIN; $year--) {
            $years[$year] = (string) $year;
        }

        return $years;
    }

    /**
     * Годы, которые уже есть у книг в каталоге.
     *
     * @return array<int, string>
     */
    public static function getExistingYearOptions()
    {
        $years = static::find()
            ->select('issue_year')
            ->distinct()
            ->orderBy(['issue_year' => SORT_DESC])
            ->column();

        $options = [];
        foreach ($years as $year) {
            $options[(int) $year] = (string) $year;
        }

        return $options;
    }

    /**
     * Публичный URL обложки либо null, если файла нет.
     *
     * @return string|null
     */
    public function getCoverUrl()
    {
        if (!$this->cover_path) {
            return null;
        }

        $fullPath = Yii::getAlias('@webroot/' . $this->cover_path);
        if (!is_file($fullPath)) {
            return null;
        }

        return Yii::getAlias('@web/' . $this->cover_path);
    }

    /**
     * ФИО авторов через запятую.
     *
     * @return string
     */
    public function getAuthorsList()
    {
        $names = [];
        foreach ($this->authors as $author) {
            $names[] = $author->full_name;
        }
        sort($names, SORT_STRING);

        return implode(', ', $names);
    }

    /**
     * Подставляет выбранных авторов в форму редактирования.
     */
    public function populateAuthorIds()
    {
        $this->authorIds = [];
        foreach ($this->authors as $author) {
            $this->authorIds[] = (int) $author->id;
        }
    }

    /**
     * Сохраняет книгу, связи с авторами и файл обложки в одной транзакции.
     *
     * @param bool $isNew нужно ли уведомлять подписчиков (только для новой книги)
     * @return bool
     */
    public function saveWithRelations($isNew = false)
    {
        $transaction = static::getDb()->beginTransaction();

        try {
            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->replaceAuthors();
            $this->storeCover();
            $this->updateAttributes(['cover_path' => $this->cover_path]);

            $transaction->commit();
        } catch (\Throwable $exception) {
            $transaction->rollBack();
            Yii::error($exception->getMessage(), __METHOD__);
            $this->addError('coverFile', 'Не удалось сохранить книгу.');
            return false;
        }

        if ($isNew) {
            $this->notifySubscribers();
        }

        return true;
    }

    /**
     * Удаляет файл обложки вместе с записью.
     *
     * {@inheritdoc}
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->deleteCoverFile();
    }

    /**
     * Перезаписывает набор авторов книги.
     */
    protected function replaceAuthors()
    {
        $this->unlinkAll('authors', true);

        foreach ((array) $this->authorIds as $authorId) {
            $author = Author::findOne($authorId);
            if ($author !== null) {
                $this->link('authors', $author);
            }
        }
    }

    /**
     * Сохраняет загруженный файл обложки в web/uploads/covers.
     */
    protected function storeCover()
    {
        if (!$this->coverFile instanceof UploadedFile) {
            return;
        }

        $directory = Yii::getAlias('@webroot/' . self::COVER_DIR);
        FileHelper::createDirectory($directory);

        $this->deleteCoverFile();

        $fileName = $this->id . '_' . uniqid('', true) . '.' . $this->coverFile->extension;
        $relativePath = self::COVER_DIR . '/' . $fileName;
        $absolutePath = Yii::getAlias('@webroot/' . $relativePath);

        if (!$this->coverFile->saveAs($absolutePath)) {
            throw new \RuntimeException('Ошибка записи файла обложки.');
        }

        $this->cover_path = $relativePath;
    }

    /**
     * Удаляет файл обложки с диска, если он есть.
     */
    protected function deleteCoverFile()
    {
        if (!$this->cover_path) {
            return;
        }

        $fullPath = Yii::getAlias('@webroot/' . $this->cover_path);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }

    /**
     * Отправляет SMS гостям, подписанным на авторов этой книги.
     *
     * Ошибка шлюза не откатывает сохранение книги.
     */
    public function notifySubscribers()
    {
        $this->refresh();

        foreach ($this->authors as $author) {
            foreach ($author->subscriptions as $subscription) {
                $text = sprintf(
                    'Новая книга "%s" автора %s',
                    $this->title,
                    $author->full_name
                );

                try {
                    Yii::$app->smsPilot->send($subscription->phone, $text);
                } catch (\Throwable $exception) {
                    Yii::error($exception->getMessage(), 'sms');
                }
            }
        }
    }
}
