<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use app\models\Author;
use app\models\Book;
use app\models\BookSearch;
use app\models\SubscriptionForm;

/**
 * Просмотр каталога для всех; CRUD книг — для роли user.
 */
class BookController extends Controller
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
     * Список книг.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new BookSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Карточка книги.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model' => $model,
            'subscriptionForms' => Yii::$app->user->isGuest ? $this->buildSubscriptionForms($model) : [],
        ]);
    }

    /**
     * Создание книги. После сохранения рассылаются SMS подписчикам авторов.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Book();

        if ($model->load(Yii::$app->request->post())) {
            $model->coverFile = UploadedFile::getInstance($model, 'coverFile');
            if ($model->saveWithRelations(true)) {
                Yii::$app->session->setFlash('success', 'Книга добавлена.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'authors' => Author::getList(),
        ]);
    }

    /**
     * Редактирование книги. SMS при правке не отправляются.
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->populateAuthorIds();

        if ($model->load(Yii::$app->request->post())) {
            $model->coverFile = UploadedFile::getInstance($model, 'coverFile');
            if ($model->saveWithRelations(false)) {
                Yii::$app->session->setFlash('success', 'Книга сохранена.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'authors' => Author::getList(),
        ]);
    }

    /**
     * Удаление книги.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'Книга удалена.');

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Book
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $model = Book::find()->where(['id' => $id])->with('authors')->one();
        if ($model === null) {
            throw new NotFoundHttpException('Книга не найдена.');
        }

        return $model;
    }

    /**
     * Формы подписки гостя на каждого автора книги.
     *
     * @param Book $book
     * @return SubscriptionForm[]
     */
    public function buildSubscriptionForms(Book $book)
    {
        if (!Yii::$app->user->isGuest) {
            return [];
        }

        $forms = [];
        foreach ($book->authors as $author) {
            $form = new SubscriptionForm();
            $form->author_id = $author->id;
            $forms[$author->id] = $form;
        }

        return $forms;
    }
}
