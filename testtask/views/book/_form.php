<?php

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var array $authors */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use app\models\Book;
use kartik\select2\Select2;

?>
<div class="book-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'issue_year')->dropDownList(Book::getYearOptions(), [
        'prompt' => 'Выберите год',
    ]) ?>
    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?php if ($authors): ?>
        <?= $form->field($model, 'authorIds')->widget(Select2::class, [
            'data' => $authors,
            'theme' => Select2::THEME_KRAJEE_BS5,
            'bsVersion' => '5.x',
            'language' => 'ru',
            'options' => [
                'placeholder' => 'Начните вводить ФИО автора',
                'multiple' => true,
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'closeOnSelect' => false,
            ],
        ]) ?>
    <?php else: ?>
        <div class="alert alert-warning">Сначала добавьте хотя бы одного автора.</div>
    <?php endif; ?>

    <?php if ($model->coverUrl): ?>
        <p>Текущая обложка:</p>
        <p><?= Html::img($model->coverUrl, ['style' => 'max-width:180px;']) ?></p>
    <?php endif; ?>

    <?= $form->field($model, 'coverFile')->fileInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success', 'disabled' => !$authors]) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
