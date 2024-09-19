<?php

namespace backend\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use common\models\User;
use common\models\AccessToken;
use common\models\ApiResponse;
use yii\data\ActiveDataProvider;

class UserController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            Yii::$app->params['controllerBehaviors'],
        );
    }
    public function actionCreate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $requestBody = Yii::$app->request->getRawBody();
        $request = json_decode($requestBody, true);

        $name = $request['name'] ?? null;
        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (!$name || !$email || !$password) {
            return ApiResponse::error('name, email and password are required');
        }

        $user = new User();
        if ($user->registerUser($name, $email, $password)) {
            $accessToken = AccessToken::generateAccessToken($user->getId());

            if ($accessToken) {
                return ApiResponse::success($accessToken->access, 'user registered');
            }
        }

        return ApiResponse::error('failed to register', $user->errors);
    }

    public function actionLogin(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $requestBody = Yii::$app->request->getRawBody();
        $request = json_decode($requestBody, true);

        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (!$email || !$password) {
            return ApiResponse::error('email and password are required');
        }

        $user = User::loginUser($email, $password);

        if ($user) {
            AccessToken::deleteAll(['userId' => $user->id]);
            $accessToken = AccessToken::generateAccessToken($user->id);

            if ($accessToken) {
                return ApiResponse::success($accessToken->accessToken, 'login successful');
            }
        }

        return ApiResponse::error('email or password are incorrect', $user->errors);
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
}
