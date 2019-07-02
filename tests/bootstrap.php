<?php

error_reporting(-1);

defined('YII_ENABLE_ERROR_HANDLER') or define('YII_ENABLE_ERROR_HANDLER', false);

defined('YII_ENV') or define('YII_ENV', 'test');
defined('YII_DEBUG') or define('YII_DEBUG', true);

defined('CC_APPCODE') or define('CC_APPCODE', 'ryder');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';
