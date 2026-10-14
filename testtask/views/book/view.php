<?php

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var app\models\SubscriptionForm[] $subscriptionForms */

use yii\bootstrap5\Html;
use yii\widgets\DetailView;

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Книги', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$canManage = Yii::$app->user->can('manageCatalog');
$isGuest = Yii::$app->user->isGuest;
$subscriptionForms = $subscriptionForms ?? [];
?>
<div class="book-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($canManage): ?>
        <p>
            <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => ['confirm' => 'Удалить книгу?', 'method' => 'post'],
            ]) ?>
        </p>
    <?php endif; ?>

    <?php if ($model->coverUrl): ?>
        <p><?= Html::img($model->coverUrl, ['alt' => $model->title, 'style' => 'max-width:240px;']) ?></p>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'title',
            'issue_year',
            'isbn',
            [
                'label' => 'Авторы',
                'format' => 'raw',
                'value' => function ($model) {
                    $links = [];
                    foreach ($model->authors as $author) {
                        $links[] = Html::a(Html::encode($author->full_name), ['author/view', 'id' => $author->id]);
                    }
                    return $links ? implode(', ', $links) : '—';
                },
            ],
            [
                'attribute' => 'description',
                'format' => 'ntext',
            ],
        ],
    ]) ?>

    <?php if ($isGuest && $model->authors): ?>
        <h2>Подписка на новые книги авторов</h2>
        <p>Подписка оформляется на автора, а не на конкретную книгу. SMS придёт, когда у выбранного автора появится новая книга.</p>

        <?php foreach ($model->authors as $author): ?>
            <h3><?= Html::encode($author->full_name) ?></h3>
            <?= $this->render('//subscription/_form', [
                'model' => $subscriptionForms[$author->id],
            ]) ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
