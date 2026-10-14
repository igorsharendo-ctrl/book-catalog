<?php

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var array $authors */

use yii\bootstrap5\Html;

$this->title = 'Новая книга';
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model, 'authors' => $authors]) ?>
</div>
