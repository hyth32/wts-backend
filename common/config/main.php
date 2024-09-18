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
    ],
];
