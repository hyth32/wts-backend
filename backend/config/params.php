<?php

use yii\filters\VerbFilter;
use yii\filters\AccessControl;

return [
    'adminEmail' => 'admin@example.com',
    'controllerBehaviors' => [
        'verbs' => [
            'class' => VerbFilter::class,
            'actions' => [
                'create' => ['POST'],
                'update' => ['PUT'],
                'delete' => ['POST'],
                'view' => ['GET'],
                'index' => ['GET'],
            ],
        ],
        'access' => [
            'class' => AccessControl::class,
            'only' => ['index', 'view', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => fn() => Yii::$app->user->identity->isAdmin(),
                ],
                ['allow' => false],
            ],
        ],
    ],
];
