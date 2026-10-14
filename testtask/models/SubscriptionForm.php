<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\Json;

/**
 * Форма подписки гостя на новые книги автора.
 */
class SubscriptionForm extends Model
{
    /**
     * @var int
     */
    public $author_id;

    /**
     * @var string
     */
    public $phone;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['author_id', 'phone'], 'required'],
            [['author_id'], 'integer'],
            [['author_id'], 'exist', 'targetClass' => Author::class, 'targetAttribute' => 'id'],
            [
                'phone',
                'validatePhone',
                'clientValidate' => 'clientValidatePhone',
            ],
            ['phone', 'validateUniqueSubscription'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'author_id' => 'Автор',
            'phone' => 'Номер телефона',
        ];
    }

    /**
     * Проверяет российский мобильный номер. Маска +7 (XXX) XXX-XX-XX на клиенте
     * не должна мешать: сравниваем только цифры 79XXXXXXXXX.
     */
    public function validatePhone($attribute)
    {
        $normalized = Subscription::normalizePhone($this->$attribute);
        if ($normalized === null || !preg_match('/^79\d{9}$/', $normalized)) {
            $this->addError($attribute, 'Укажите российский номер, например +7 (900) 123-45-67.');
            return;
        }

        $this->$attribute = $normalized;
    }

    /**
     * Клиентская проверка: та же логика, что и на сервере, по цифрам, а не по маске.
     *
     * @return string
     */
    public function clientValidatePhone()
    {
        $message = Json::htmlEncode('Укажите российский номер, например +7 (900) 123-45-67.');

        return <<<JS
var digits = String(value || '').replace(/\\D+/g, '');
if (digits.charAt(0) === '8' || digits.charAt(0) === '7') {
    digits = '7' + digits.substring(1);
} else if (digits.length === 10) {
    digits = '7' + digits;
}
if (!/^79\\d{9}$/.test(digits)) {
    messages.push($message);
}
JS;
    }

    /**
     * Запрещает повторную подписку того же номера на того же автора.
     */
    public function validateUniqueSubscription()
    {
        if ($this->hasErrors()) {
            return;
        }

        $exists = Subscription::find()
            ->where(['author_id' => $this->author_id, 'phone' => $this->phone])
            ->exists();

        if ($exists) {
            $this->addError('phone', 'Этот номер уже подписан на выбранного автора.');
        }
    }

    /**
     * Сохраняет подписку гостя.
     *
     * @return bool
     */
    public function subscribe()
    {
        if (!$this->validate()) {
            return false;
        }

        $subscription = new Subscription();
        $subscription->author_id = $this->author_id;
        $subscription->phone = $this->phone;

        return $subscription->save();
    }
}
