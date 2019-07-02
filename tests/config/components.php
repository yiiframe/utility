<?php declare(strict_types=1);

use yii\db\Connection as DbConnection;
//use yii\web\Cookie;
use yii\log\Dispatcher as LogDispatcher;
use yii\log\DbTarget;
use yii\log\FileTarget;
use yii\i18n\Formatter as I18NFormatter;
use yii\i18n\I18N;
use yii\i18n\MessageFormatter;
use yii\i18n\PhpMessageSource;
use yii\caching\FileCache;
use yii\rbac\DbManager;
//use yii\web\Request as WebRequest;
//use yii\web\Response as WebResponse;
//use yii\web\HtmlResponseFormatter;
//use yii\web\JsonResponseFormatter;
//use yii\web\XmlResponseFormatter;
use yii\web\ErrorHandler as WebErrorHandler;
use yii\console\ErrorHandler as ConsoleErrorHandler;

//use yii\mutex\MysqlMutex;
//use yii\web\JsonParser;
//use yii\web\User as WebUser;
//use yii\helpers\ArrayHelper;

use yii\web\UrlManager as YiiWebUrlManager;
use yii\web\UrlRule as YiiWebUrlRule;
use yii\rest\UrlRule as YiiRestUrlRule;

$components = [];

/**
 * 数据库组件.
 *
 * 配置示例：
 * [
 *   'dsn' => 'mysql:dbname=example;host=IP;charset=utf8mb4;',
 *   'password' => 'example',
 *   'tablePrefix' => 'example_',
 *   'username' => 'root',
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[DbConnection::class] = static function (array $config): array {
    return array_merge([
        'class' => DbConnection::class,
        'attributes' => [
            PDO::ATTR_CASE => PDO::CASE_NATURAL, // 保留数据库驱动返回的列名
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // 错误报告模式：exception
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL, // 空字符串<=>NULL：保持原样
            PDO::ATTR_STRINGIFY_FETCHES => false, // 提取的时候将数值转换为字符串：No
            PDO::ATTR_TIMEOUT => 3, // 数据库连接超时时间：3s
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true, // 使用缓冲查询
        ],
        'charset' => 'utf8mb4',
        // 'commandClass' => , @deprecated
        // 'commandMap' => ,
        'driverName' => 'mysql',
        // 'emulatePrepare' => , // 对于SQL语句的预处理，是由PHP本地执行还是由MYSQL服务器执行
        'enableLogging' => YII_ENV !== 'prod', // 生产环境提升性能 https://github.com/yiisoft/yii2/issues/12528
        'enableProfiling' => YII_ENV !== 'prod', // 生产环境提升性能 https://github.com/yiisoft/yii2/issues/12528
        'enableQueryCache' => true,
        'enableSavepoint' => true,
        'enableSchemaCache' => true,
        'enableSlaves' => true,
        'masterConfig' => [],
        'masters' => [],
        // 'pdo' => ,
        'pdoClass' => PDO::class,
        // 'queryBuilder' => ,
        'queryCache' => 'cache',
        'queryCacheDuration' => YII_ENV === 'prod' ? 3600 : 600,
        'schemaCache' => 'cache',
        'schemaCacheDuration' => YII_ENV === 'prod' ? 3600 : 600,
        'schemaCacheExclude' => [], // 缓存排除
        // 'schemaMap' => ,
        'serverRetryInterval' => 600,
        'serverStatusCache' => 'cache',
        'shuffleMasters' => true,
        'slaveConfig' => [],
        'slaves' => [],
    ], $config);
};

/**
 * Cookie组件.
 *
 * 配置示例：
 * [
 *   'name' => '_csrf',
 *   'secure' => true,
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[Cookie::class] = static function (array $config): array {
//    return array_merge([
//        'class' => Cookie::class,
//        'name' => $config['name'],
//        'expire' => time() + 7200,
//        'path' => '/',
//        'domain' => CC_FULLDOMAIN,
//        'httpOnly' => true,
//        'secure' => $config['secure'] ?? (YII_ENV === 'prod'),
//        'sameSite' => PHP_VERSION_ID >= 70300 ? Cookie::SAME_SITE_LAX : null,
//    ], $config);
//};

/**
 * Redis组件.
 *
 * 配置示例：
 * [
 *   'database' => 2,
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[RedisConnection::class] = static function (array $config): array {
//    return array_merge([
//        'class' => RedisConnection::class,
//        'hostname' => CC_WINOS ? 'localhost' : 'redis',
//        'port' => 6379,
//        'connectionTimeout' => null,
//    ], $config);
//};

/**
 * 日志组件.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[LogDispatcher::class] = static function (array $config = []): array {
    return array_merge([
        'class' => LogDispatcher::class,
        'traceLevel' => 0,
        'flushInterval' => YII_ENV === 'prod' ? 1000 : 0,
        'targets' => [
            [
                'class' => DbTarget::class,
                'categories' => [],
                'enabled' => YII_ENV === 'prod',
                'exportInterval' => 1000,
                'except' => [
                    'yii\web\HttpException:404',
                    'yii\web\HttpException:403',
                ],
                'levels' => ['error'],
                'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'],
                'messages' => [],
                'microtime' => false,
                'logTable' => '{{%errlog}}',
            ],
            [
                'class' => FileTarget::class,
                'categories' => [],
                'enabled' => true,
                'exportInterval' => 1000,
                'except' => [
                    'yii\web\HttpException:404',
                    'yii\web\HttpException:403',
                ],
                'levels' => YII_ENV === 'prod' ? ['warning'] : ['warning', 'error'],
                'logVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION', '_SERVER'],
                'messages' => [],
                'microtime' => false,
                'dirMode' => 0775,
                'enableRotation' => true,
                'fileMode' => null,
                'logFile' => '@runtime/logs/app.log',
                'maxFileSize' => 10240, // 10MB
                'maxLogFiles' => 5,
                'rotateByCopy' => true,
            ],
        ],
    ], $config);
};

/**
 * 格式化组件.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[I18NFormatter::class] = static function (array $config = []): array {
    return array_merge([
        'class' => I18NFormatter::class,
        'booleanFormat' => ['No', 'Yes'],
        'dateFormat' => 'medium',
        'datetimeFormat' => 'medium',
        'decimalSeparator' => '.',
        'defaultTimeZone' => 'Asia/Shanghai',
        'thousandSeparator' => ',',
        'timeFormat' => 'medium',
    ], $config);
};

/**
 * 国际化组件.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[I18N::class] = static function (array $config = []): array {
    return array_merge([
        'class' => I18N::class,
        'messageFormatter' => [
            'class' => MessageFormatter::class,
        ],
        'translations' => [
            'app*' => [
                'class' => PhpMessageSource::class,
                'basePath' => '@app/messages',
                'fileMap' => [
                    'app' => 'app.php',
                    'app/error' => 'error.php',
                ],
            ],
        ],
    ], $config);
};

/**
 * 缓存组件（redis驱动）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[RedisCache::class] = static function (array $config = []) use ($components): array {
//    return array_merge([
//        'class' => RedisCache::class,
//        'defaultDuration' => 0,
//        'keyPrefix' => strtolower(CC_APPCODE),
//        'redis' => $components[RedisConnection::class]([
//            'database' => 6,
//        ]),
//    ], $config);
//};

/**
 * 缓存组件（file驱动）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[FileCache::class] = static function (array $config = []): array {
    return array_merge([
        'class' => FileCache::class,
        'defaultDuration' => 0,
        'keyPrefix' => strtolower(CC_APPCODE),
    ], $config);
};

/**
 * 错误处理组件（web）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[WebErrorHandler::class] = static function (array $config = []): array {
    return array_merge([
        'class' => WebErrorHandler::class,
        'displayVars' => ['_GET', '_POST', '_FILES', '_COOKIE', '_SESSION'],
    ], $config);
};

/**
 * 错误处理组件（console）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[ConsoleErrorHandler::class] = static function (array $config = []): array {
    return array_merge([
        'class' => ConsoleErrorHandler::class,
    ], $config);
};

/**
 * 请求处理组件（web）.
 *
 * 配置示例：
 * [
 *   'cookieValidationKey' => '...',
 *   'csrfCookie.name' => '_csrf',（可选）
 *   'csrfCookie.secure' => true,（可选）
 *   'csrfParam' => '_csrf',（可选）
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[WebRequest::class] = static function (array $config) use ($components): array {
//    $config = array_merge([
//        'class' => WebRequest::class,
//        'parsers' => [
//            'application/json' => JsonParser::class,
//            'text/json' => JsonParser::class,
//            'application/xml' => XmlParser::class,
//            'text/xml' => XmlParser::class,
//        ],
//        'isConsoleRequest' => false,
//        'csrfCookie' => $components[Cookie::class]([
//            'name' => $config['csrfCookie.name'] ?? '_csrf',
//            'secure' => $config['csrfCookie.secure'] ?? (YII_ENV === 'prod'),
//        ]),
//        'csrfParam' => $config['csrfParam'] ?? '_csrf',
//    ], $config);
//
//    unset(
//        $config['csrfCookie.name'],
//        $config['csrfCookie.secure'],
//        $config['csrfParam']
//    );
//
//    return $config;
//};

/**
 * 响应处理组件（web）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[WebResponse::class] = static function (array $config = []): array {
//    return array_merge([
//        'class' => WebResponse::class,
//        'formatters' => [
//            WebResponse::FORMAT_HTML => [
//                'class' => HtmlResponseFormatter::class,
//                'contentType' => 'text/html',
//            ],
//            WebResponse::FORMAT_JSON => [
//                'class' => JsonResponseFormatter::class,
//                'contentType' => JsonResponseFormatter::CONTENT_TYPE_JSON,
//                'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
//                'prettyPrint' => YII_ENV !== 'prod',
//            ],
//            WebResponse::FORMAT_XML => [
//                'class' => XmlResponseFormatter::class,
//                'contentType' => 'application/xml',
//                'version' => '1.0',
//                'encoding' => 'UTF-8',
//                'rootTag' => 'xml',
//                'itemTag' => 'item',
//            ],
//            WebResponse::FORMAT_JSONP => [
//                'class' => JsonResponseFormatter::class,
//                'prettyPrint' => YII_ENV !== 'prod',
//                'encodeOptions' => JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
//            ],
//        ],
//        'format' => WebResponse::FORMAT_HTML,
//        'charset' => 'UTF-8',
//        'on beforeSend' => static function ($event): void {
//            /** @var $response WebResponse */
//            $response = $event->sender;
//
//            /**
//             * 处理返回header头.
//             */
//            $contentType = Yii::$app->getRequest()->getContentType();
//            $comaPosition = strpos($contentType, ';');
//            if ($comaPosition !== false) {
//                $contentType = substr($contentType, 0, $comaPosition);
//            }
//            if ($contentType === 'application/json' || $contentType === 'text/json') {
//                $response->format = WebResponse::FORMAT_JSON;
//            } elseif ($contentType === 'application/xml' || $contentType === 'text/xml') {
//                $response->format = WebResponse::FORMAT_XML;
//            }
//
//            if ($response->format === WebResponse::FORMAT_JSON) {
//                $originalData = $response->data;
//                if (empty(Yii::$app->getRequest()->get('no_optimization'))) {
//                    $response->data = [
//                        'isOk' => $response->getIsSuccessful(),
//                        'retCode' => $response->getStatusCode(),
//                    ];
//
//                    $toUrl = $response->getHeaders()->get('Location');
//                    if ($toUrl !== null) {
//                        $response->getHeaders()->remove('Location');
//                        $response->data['toUrl'] = $toUrl;
//                    }
//
//                    $response->data['data'] = $originalData;
//                    if ($response->data['isOk']) {
//                        if (isset($originalData['err'])) {
//                            $response->data['isOk'] = false;
//                            $response->data['retCode'] = 400;
//                        }
//                    }
//
//                    $response->setStatusCode(200);
//                }
//            }
//        },
//    ], $config);
//};

/**
 * 会话控制组件（Redis驱动）.
 *
 * 配置示例：
 * [
 *   'cookieParams.secure' => true,（可选）
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[RedisSession::class] = static function (array $config = []) use ($components): array {
//    $config = array_merge([
//        'class' => RedisSession::class,
//        'keyPrefix' => strtolower(CC_APPCODE),
//        'cookieParams' => [
//            'lifetime' => 7200,
//            'path' => '/',
//            'domain' => CC_FULLDOMAIN,
//            'httpOnly' => true,
//            'secure' => $config['cookieParams.secure'] ?? (YII_ENV === 'prod'),
//            'sameSite' => PHP_VERSION_ID >= 70300 ? Cookie::SAME_SITE_LAX : null,
//        ],
//        'redis' => $components[RedisConnection::class]([
//            'database' => 5,
//        ]),
//        'timeout' => 7200,
//    ], $config);
//
//    unset(
//        $config['cookieParams.secure']
//    );
//
//    return $config;
//};

/**
 * 用户组件.
 *
 * 配置示例：
 * [
 *   'identityCookie.secure' => true,（可选）
 *   'identityCookie.name' => 'hello_identity',（可选）
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[WebUser::class] = static function (array $config = []) use ($components): array {
//    $config = array_merge([
//        'class' => WebUser::class,
//        'authTimeout' => 7200,
//        'autoRenewCookie' => WXMINI_VERSION === null,
//        'enableAutoLogin' => WXMINI_VERSION === null,
//        'enableSession' => WXMINI_VERSION === null,
//        'identityClass' => ModelUser::class,
//        'identityCookie' => $components[Cookie::class]([
//            'name' => $config['identityCookie.name'] ?? (
//                    '_'
//                    . str_replace('-', '_', CC_SUBDOMAIN)
//                    . '_identity'
//                ),
//            'secure' => $config['identityCookie.secure'] ?? (YII_ENV === 'prod'),
//        ]),
//        'loginUrl' => null,
//    ], $config);
//
//    unset(
//        $config['identityCookie.name'],
//        $config['identityCookie.secure']
//    );
//
//    return $config;
//};

/**
 * 消息队列组件（数据库驱动）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[DbQueue::class] = static function (array $config = []): array {
//    return array_merge([
//        'class' => DbQueue::class,
//        'tableName' => '{{%queue}}',
//        'channel' => 'default',
//        'mutex' => MysqlMutex::class,
//        'attempts' => 3,
//        'ttr' => 300, // 秒
//        'as log' => QueueLogBehavior::class,
//    ], $config);
//};

/**
 * 邮件组件.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[SwiftMailer::class] = static function (array $config = []): array {
//    return ArrayHelper::merge([
//        'class' => SwiftMailer::class,
//        'viewPath' => '@app/mail',
//        'htmlLayout' => '@app/mail/layouts/html',
//        'textLayout' => '@app/mail/layouts/text',
//        'useFileTransport' => false,
//        'transport' => [
//            'class' => Swift_SmtpTransport::class,
//            'timeout' => 45,
//        ],
//        'messageConfig' => [
//            'charset' => 'UTF-8',
//        ],
//    ], $config);
//};

/**
 * RBAC权限组件（数据库驱动）.
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[DbManager::class] = static function (array $config = []): array {
    return array_merge([
        'class' => DbManager::class,
        'cache' => 'cache',
    ], $config);
};

/**
 * 路由控制组件.
 *
 * 配置示例：
 * [
 *   'rules.controller' => [
 *
 *   ]
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
$components[YiiWebUrlManager::class] = static function (array $config): array {
    $config = array_merge([
        'class' => YiiWebUrlManager::class,
        'cache' => 'cache',
        'enablePrettyUrl' => true,
        'enableStrictParsing' => false,
        'routeParam' => 'r',
        'suffix' => null,
        'showScriptName' => false,
        'ruleConfig' => [
            'class' => YiiWebUrlRule::class,
        ],
        'rules' => [
            [
                'class' => YiiRestUrlRule::class,
                'pluralize' => false,
                'controller' => $config['rules.controller'],
            ],
        ],
    ], $config);

    unset($config['rules.controller']);

    return $config;
};

/**
 * 路由控制组件2.
 *
 * 配置示例：
 * [
 *   'rules.controller' => [
 *
 *   ]
 * ]
 *
 * @param array $config 配置数组
 * @return array 合并后的配置数组
 */
//$components[CodemixUrlManager::class] = static function (array $config): array {
//    $config = array_merge([
//        'class' => CodemixUrlManager::class,
//        'cache' => 'cache',
//        'enablePrettyUrl' => true,
//        'enableStrictParsing' => false,
//        'routeParam' => 'r',
//        'suffix' => null,
//        'showScriptName' => false,
//        'languages' => CC_LANGS,
//        'languageCookieOptions' => [
//            'domain' => CC_FULLDOMAIN,
//            'path' => '/',
//            'secure' => YII_ENV === 'prod',
//        ],
//        'enableDefaultLanguageUrlCode' => true,
//        'keepUppercaseLanguageCode' => true,
//        'ruleConfig' => [
//            'class' => YiiWebUrlRule::class,
//        ],
//        'rules' => [
//            [
//                'class' => YiiRestUrlRule::class,
//                'pluralize' => false,
//                'controller' => $config['rules.controller'],
//            ],
//        ],
//    ], $config);
//
//    unset($config['rules.controller']);
//
//    return $config;
//};

return $components;
