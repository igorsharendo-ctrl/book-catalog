<?php

/** @var yii\web\View $this */
/** @var app\models\ReportForm $model */
/** @var array $rows */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'ТОП 10 авторов';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="report-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Авторы с наибольшим числом книг за выбранный год выпуска.</p>

    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'year')->dropDownList(\app\models\Book::getExistingYearOptions(), [
                'prompt' => 'Выберите год',
            ]) ?>
        </div>
    </div>
    <div class="form-group mb-3">
        <?= Html::submitButton('Показать', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>

    <?php if (!$model->hasErrors()): ?>
        <?php if (!$rows): ?>
            <p>За <?= (int) $model->year ?> год книги не найдены.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead>
                <tr>
                    <th>Место</th>
                    <th>Автор</th>
                    <th>Книг за год</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= Html::a(Html::encode($row['full_name']), ['author/view', 'id' => $row['id']]) ?></td>
                        <td><?= (int) $row['books_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>
