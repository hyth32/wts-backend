<?php

namespace backend\controllers;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use common\models\User;
use yii\data\ActiveDataProvider;
use common\services\UserService;
use common\services\AccessTokenService;

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

    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = $this->getRequestBody();

        if ($this->validateCreateRequest($request)) {
            $user = $this->userService->createUser($request['name'], $request['email'], $request['password']);

            if ($user) {
                $accessToken = $this->accessTokenService->generateAccessToken($user->getId());
                return $this->successResponse($accessToken->accessToken, 'User registered');
            }

            return $this->errorResponse('failed to register', $user->errors);
        }

        return $this->errorResponse('name, email and password are required');
    }

    public function actionLogin(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = $this->getRequestBody();

        if ($this->validateLoginRequest($request)) {
            $user = $this->userService->loginUser($request['email'], $request['password']);

            if ($user) {
                $accessToken = $this->accessTokenService->generateAccessToken($user->getId());
                return $this->successResponse($accessToken->accessToken, 'login successful');
            }

            return $this->errorResponse('email or password are incorrect', $user->errors);
        }

        return $this->errorResponse('email and password are required');
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

    private function validateCreateRequest($request): bool
    {
        return !empty($request['name']) && !empty($request['email']) && !empty($request['password']);
    }

    private function validateLoginRequest($request): bool
    {
        return !empty($request['email']) && !empty($request['password']);
    }
}
