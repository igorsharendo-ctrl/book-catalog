<?php

namespace app\controllers;

use Yii;
use yii\db\IntegrityException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use app\models\Author;
use app\models\AuthorSearch;
use app\models\SubscriptionForm;

/**
 * Просмотр авторов для всех; CRUD — для роли user.
 */
class AuthorController extends Controller
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
                        'actions' => ['index', 'view'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['manageCatalog'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Список авторов.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AuthorSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Карточка автора и форма подписки для гостя.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $subscriptionForm = new SubscriptionForm();
        $subscriptionForm->author_id = $model->id;

        return $this->render('view', [
            'model' => $model,
            'subscriptionForm' => $subscriptionForm,
        ]);
    }

    /**
     * Создание автора.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Author();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор добавлен.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Редактирование автора.
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Автор сохранён.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление автора. Запрещено, пока на него ссылаются книги.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        try {
            $model->delete();
            Yii::$app->session->setFlash('success', 'Автор удалён.');
        } catch (IntegrityException $exception) {
            Yii::$app->session->setFlash('error', 'Нельзя удалить автора, пока за ним числятся книги.');
        }

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Author
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Author::find()->where(['id' => $id])->with('books')->one();
        if ($model === null) {
            throw new NotFoundHttpException('Автор не найден.');
        }

        return $model;
    }
}
