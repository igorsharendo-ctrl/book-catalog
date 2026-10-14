<?php

/** @var yii\web\View $this */
/** @var app\models\Author $model */
/** @var app\models\SubscriptionForm $subscriptionForm */

use yii\bootstrap5\Html;
use yii\widgets\DetailView;

$this->title = $model->full_name;
$this->params['breadcrumbs'][] = ['label' => 'Авторы', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$canManage = Yii::$app->user->can('manageCatalog');
$isGuest = Yii::$app->user->isGuest;
?>
<div class="author-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($canManage): ?>
        <p>
            <?= Html::a('Изменить', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Удалить', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => ['confirm' => 'Удалить автора?', 'method' => 'post'],
            ]) ?>
        </p>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'full_name',
            [
                'label' => 'Книг в каталоге',
                'value' => $model->booksCount,
            ],
        ],
    ]) ?>

    <h2>Книги</h2>
    <?php if (!$model->books): ?>
        <p>Книг пока нет.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($model->books as $book): ?>
                <li>
                    <?= Html::a(Html::encode($book->title), ['book/view', 'id' => $book->id]) ?>
                    (<?= (int) $book->issue_year ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <?php if ($isGuest): ?>
        <h2 id="subscription">Подписка на новые книги</h2>
        <p>Укажите номер телефона. При появлении новой книги этого автора придёт SMS.</p>
        <?= $this->render('//subscription/_form', ['model' => $subscriptionForm]) ?>
    <?php endif; ?>
</div>
