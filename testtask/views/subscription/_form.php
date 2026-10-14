<?php

/** @var yii\web\View $this */
/** @var app\models\SubscriptionForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\widgets\PhoneMaskInput;

$formId = 'sub-form-' . (int) $model->author_id;
$phoneId = 'sub-phone-' . (int) $model->author_id;
?>
<div class="subscription-form">
    <?php $form = ActiveForm::begin([
        'action' => ['/subscription/create'],
        'id' => $formId,
    ]); ?>
    <?= $form->field($model, 'author_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'phone')->widget(PhoneMaskInput::class, [
        'options' => ['id' => $phoneId],
    ]) ?>
    <div class="form-group">
        <?= Html::submitButton('Подписаться', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
