<?php

namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Подписка гостя на новые книги выбранного автора.
 *
 * @property int $id
 * @property int $author_id
 * @property string $phone
 * @property int $created_at
 *
 * @property Author $author
 */
class Subscription extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%subscription}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'phone'], 'required'],
            [['author_id'], 'integer'],
            [['phone'], 'string', 'max' => 15],
            [['author_id'], 'exist', 'targetClass' => Author::class, 'targetAttribute' => 'id'],
            [
                ['author_id', 'phone'],
                'unique',
                'targetAttribute' => ['author_id', 'phone'],
                'message' => 'Этот номер уже подписан на выбранного автора.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'author_id' => 'Автор',
            'phone' => 'Телефон',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        if ($insert && $this->created_at === null) {
            $this->created_at = time();
        }

        return true;
    }

    /**
     * Автор, на книги которого оформлена подписка.
     *
     * @return ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }

    /**
     * Приводит ввод к международному формату 7XXXXXXXXXX.
     *
     * @param string $phone
     * @return string|null
     */
    public static function normalizePhone($phone)
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 11 && ($digits[0] === '8' || $digits[0] === '7')) {
            return '7' . substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            return '7' . $digits;
        }

        return null;
    }
}
