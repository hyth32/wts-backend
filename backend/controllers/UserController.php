<?php

namespace backend\controllers;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use common\models\User;
use yii\data\ActiveDataProvider;
use backend\services\UserService;
use backend\services\AccessTokenService;

class UserController extends BaseController
{
    protected $userService;
    protected $accessTokenService;

    public function __construct($id, $module, UserService $userService, AccessTokenService $accessTokenService, $config = [])
    {
        $this->userService = $userService;
        $this->accessTokenService = $accessTokenService;
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        return [
            'create' => [
                'class' => 'backend\actions\user\CreateUserAction',
                'userService' => $this->userService,
                'accessTokenService' => $this->accessTokenService,
            ],
            'login' => [
                'class' => 'backend\actions\user\LoginUserAction',
                'userService' => $this->userService,
                'accessTokenService' => $this->accessTokenService,
            ],
        ];
    }

    public function actionIndex(): string
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id): string
    {
        Yii::$app->response->format = Response::FORMAT_HTML;

        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionUpdate($id): Response|string
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionDelete($id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id): User
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function validateCreateRequest($request): bool
    {
        return !empty($request['name']) && !empty($request['email']) && !empty($request['password']);
    }

    public function validateLoginRequest($request): bool
    {
        return !empty($request['email']) && !empty($request['password']);
    }
}
