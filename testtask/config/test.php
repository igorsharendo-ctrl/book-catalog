<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/test_db.php';

return [
    'id' => 'book-catalog-tests',
    'name' => 'Каталог книг',
    'language' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@uploads' => '@app/web/uploads',
    ],
    'components' => [
        'db' => $db,
        'assetManager' => [
            'basePath' => __DIR__ . '/../web/assets',
        ],
        'urlManager' => [
            'showScriptName' => true,
        ],
        'user' => [
            'identityClass' => 'app\models\User',
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'itemFile' => '@app/rbac/items.php',
            'assignmentFile' => '@app/rbac/assignments.php',
            'ruleFile' => '@app/rbac/rules.php',
        ],
        'smsPilot' => [
            'class' => 'app\components\SmsPilot',
            'apiKey' => $params['smsPilotApiKey'],
        ],
        'request' => [
            'cookieValidationKey' => 'test',
            'enableCsrfValidation' => false,
        ],
    ],
    'params' => $params,
];
