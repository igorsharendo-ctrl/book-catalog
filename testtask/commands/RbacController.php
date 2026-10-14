<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Инициализация RBAC: роль user и право manageCatalog.
 *
 * Файлы ролей уже лежат в rbac/. Команда нужна, чтобы пересоздать их при сбросе.
 */
class RbacController extends Controller
{
    /**
     * Создаёт роль пользователя каталога и выдаёт её учётной записи с id=1.
     *
     * @return int
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;
        $auth->removeAll();

        $manageCatalog = $auth->createPermission('manageCatalog');
        $manageCatalog->description = 'Добавление, редактирование и удаление книг и авторов';
        $auth->add($manageCatalog);

        $user = $auth->createRole('user');
        $user->description = 'Авторизованный пользователь каталога';
        $auth->add($user);
        $auth->addChild($user, $manageCatalog);
        $auth->assign($user, 1);

        $this->stdout("RBAC инициализирован.\n");

        return ExitCode::OK;
    }
}
