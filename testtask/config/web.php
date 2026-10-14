<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$cookieKey = getenv('COOKIE_VALIDATION_KEY');
if (!is_string($cookieKey) || strlen($cookieKey) < 32) {
    throw new \RuntimeException('Set COOKIE_VALIDATION_KEY to a random value of at least 32 characters.');
}

$config = [
    'id' => 'book-catalog',
    'name' => 'Каталог книг',
    'language' => 'ru-RU',
    'sourceLanguage' => 'en-US',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'defaultRoute' => 'book/index',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@uploads' => '@app/web/uploads',
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => $cookieKey,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => ['site/login'],
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'itemFile' => '@app/rbac/items.php',
            'assignmentFile' => '@app/rbac/assignments.php',
            'ruleFile' => '@app/rbac/rules.php',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logVars' => [],
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning', 'info'],
                    'categories' => ['sms'],
                    'logFile' => '@runtime/logs/sms.log',
                    'logVars' => [],
                ],
            ],
        ],
        'db' => $db,
        'smsPilot' => [
            'class' => 'app\components\SmsPilot',
            'apiKey' => $params['smsPilotApiKey'],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                '' => 'book/index',
                'login' => 'site/login',
                'logout' => 'site/logout',
                'report' => 'report/index',
                'books' => 'book/index',
                'books/<id:\d+>' => 'book/view',
                'books/create' => 'book/create',
                'books/<id:\d+>/<action:(update|delete)>' => 'book/<action>',
                'authors' => 'author/index',
                'authors/<id:\d+>' => 'author/view',
                'authors/create' => 'author/create',
                'authors/<id:\d+>/<action:(update|delete)>' => 'author/<action>',
                '<controller:[\w-]+>/<action:[\w-]+>' => '<controller>/<action>',
            ],
        ],
        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'locale' => 'ru-RU',
            'defaultTimeZone' => 'Europe/Moscow',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
