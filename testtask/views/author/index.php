<?php

/** @var yii\web\View $this */
/** @var app\models\AuthorSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Авторы';
$this->params['breadcrumbs'][] = $this->title;

$canManage = Yii::$app->user->can('manageCatalog');
?>
<div class="author-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($canManage): ?>
        <p><?= Html::a('Добавить автора', ['create'], ['class' => 'btn btn-success']) ?></p>
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'full_name',
            [
                'label' => 'Книг',
                'value' => function ($model) {
                    return $model->booksCount;
                },
            ],
            [
                'label' => 'Подписка',
                'format' => 'raw',
                'visible' => Yii::$app->user->isGuest,
                'value' => function ($model) {
                    return Html::a('Подписаться', ['view', 'id' => $model->id, '#' => 'subscription']);
                },
            ],
            [
                'class' => ActionColumn::class,
                'template' => $canManage ? '{view} {update} {delete}' : '{view}',
                'urlCreator' => function ($action, $model) {
                    return Url::to([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]) ?>
</div>
