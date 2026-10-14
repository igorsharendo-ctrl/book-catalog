<?php

/** @var yii\web\View $this */
/** @var app\models\BookSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

use yii\bootstrap5\Html;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'Книги';
$this->params['breadcrumbs'][] = $this->title;

$canManage = Yii::$app->user->can('manageCatalog');
?>
<div class="book-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($canManage): ?>
        <p><?= Html::a('Добавить книгу', ['create'], ['class' => 'btn btn-success']) ?></p>
    <?php endif; ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'cover_path',
                'format' => 'raw',
                'filter' => false,
                'value' => function ($model) {
                    if (!$model->coverUrl) {
                        return '—';
                    }
                    return Html::img($model->coverUrl, [
                        'alt' => $model->title,
                        'style' => 'max-height:60px;max-width:45px;',
                    ]);
                },
            ],
            'title',
            [
                'attribute' => 'issue_year',
                'filter' => \app\models\Book::getExistingYearOptions(),
                'filterInputOptions' => ['class' => 'form-select', 'prompt' => 'Все годы'],
            ],
            [
                'attribute' => 'authorName',
                'label' => 'Авторы',
                'value' => function ($model) {
                    return $model->authorsList ?: '—';
                },
            ],
            'isbn',
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
