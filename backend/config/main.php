<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-backend',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
	'db' => [
	    'class' => 'yii\db\Connection',
	    'dsn' => 'mysql:host=localhost;dbname=wtsBackend',
	    'username' => 'hyth',
	    'password' => '1',
	    'charset' => 'utf8',
	],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
		'POST /user/register' => 'user/register',
		'POST /user/login' => 'user/login',
'POST /user/register' => 'user/register',
        'POST /user/login' => 'user/login',
        'POST /post/publish' => 'post/publish',
        'GET /user' => 'user/index',
        'GET /user/view/<id:\d+>' => 'user/view',
        'POST /user/create' => 'user/create',
        'PUT /user/update/<id:\d+>' => 'user/update',
        'DELETE /user/delete/<id:\d+>' => 'user/delete',
            ],
        ],
    ],
    'params' => $params,
];
