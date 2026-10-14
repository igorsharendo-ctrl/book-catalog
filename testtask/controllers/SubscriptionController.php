<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use app\models\SubscriptionForm;

/**
 * Оформление подписки гостя на конкретного автора.
 */
class SubscriptionController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Принимает форму с карточки автора.
     *
     * @return Response
     */
    public function actionCreate()
    {
        $model = new SubscriptionForm();

        if ($model->load(Yii::$app->request->post()) && $model->subscribe()) {
            Yii::$app->session->setFlash('success', 'Подписка оформлена. SMS придёт при появлении новой книги автора.');
            return $this->redirect(['author/view', 'id' => $model->author_id]);
        }

        Yii::$app->session->setFlash('error', $model->getFirstError('phone') ?: 'Не удалось оформить подписку.');

        if ($model->author_id) {
            return $this->redirect(['author/view', 'id' => $model->author_id]);
        }

        return $this->redirect(['author/index']);
    }
}
