<?php
define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
require __DIR__ .'/../vendor/autoload.php';
if (!getenv('DEMO_USER_PASSWORD') || strlen(getenv('DEMO_USER_PASSWORD')) < 12) {
    throw new \RuntimeException('Set DEMO_USER_PASSWORD to the password used when seeding the test database.');
}
