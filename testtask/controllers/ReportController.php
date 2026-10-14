<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\ReportForm;

/**
 * Отчёт «ТОП 10 авторов по числу книг за год». Доступен всем.
 */
class ReportController extends Controller
{
    /**
     * Форма выбора года и таблица результата.
     *
     * @return string
     */
    public function actionIndex()
    {
        $model = new ReportForm();
        $model->load(Yii::$app->request->get());
        $model->validate();

        return $this->render('index', [
            'model' => $model,
            'rows' => $model->hasErrors() ? [] : $model->getRows(),
        ]);
    }
}
