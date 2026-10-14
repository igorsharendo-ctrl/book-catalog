<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * Автор книги. Не связан с учётной записью пользователя.
 *
 * @property int $id
 * @property string $full_name
 * @property int $created_at
 * @property int $updated_at
 *
 * @property Book[] $books
 * @property Subscription[] $subscriptions
 * @property-read int $booksCount
 */
class Author extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%author}}';
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
        return [
            [['full_name'], 'required'],
            [['full_name'], 'trim'],
            [['full_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'full_name' => 'ФИО',
            'created_at' => 'Создан',
            'updated_at' => 'Обновлён',
        ];
    }

    /**
     * Книги автора.
     *
     * @return ActiveQuery
     */
    public function getBooks()
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('{{%book_author}}', ['author_id' => 'id']);
    }

    /**
     * Подписки гостей на этого автора.
     *
     * @return ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::class, ['author_id' => 'id']);
    }

    /**
     * Количество книг автора.
     *
     * @return int
     */
    public function getBooksCount()
    {
        return (int) $this->getBooks()->count();
    }

    /**
     * Карта id => ФИО для выпадающих списков и чекбоксов.
     *
     * @return array<int, string>
     */
    public static function getList()
    {
        return static::find()
            ->select(['full_name', 'id'])
            ->indexBy('id')
            ->orderBy(['full_name' => SORT_ASC])
            ->column();
    }

    /**
     * ТОП авторов по числу книг за указанный год.
     *
     * @param int $year
     * @param int $limit
     * @return array<int, array{id:int, full_name:string, books_count:int}>
     */
    public static function getTopByYear($year, $limit = 10)
    {
        return static::find()
            ->alias('a')
            ->select([
                'a.id',
                'a.full_name',
                'books_count' => new Expression('COUNT(DISTINCT b.id)'),
            ])
            ->innerJoin('{{%book_author}} ba', 'ba.author_id = a.id')
            ->innerJoin('{{%book}} b', 'b.id = ba.book_id')
            ->where(['b.issue_year' => (int) $year])
            ->groupBy(['a.id', 'a.full_name'])
            ->orderBy([
                'books_count' => SORT_DESC,
                'a.full_name' => SORT_ASC,
            ])
            ->limit($limit)
            ->asArray()
            ->all();
    }
}
