<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => \yii\caching\FileCache::class,
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
	'POST /post/publish' => 'post/publish',
	'GET /post/get-posts' => 'post/get-posts',
	'GET /post/get-posts/<userId:\d+>' => 'post/get-user-posts',
    ],
],

    ],
];
