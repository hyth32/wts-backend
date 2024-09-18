<?php

namespace backend\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use common\models\User;
use common\models\AccessToken;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class UserController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'create' => ['POST'],
		                'update' => ['PUT'],
        		        'delete' => ['DELETE'],
						'view' => ['GET'],
                		'index' => ['GET'],
                    ],
                ],
            ]
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

        if (!$name || !$email || !$password)
        {
            return [
                'status' => 'error',
                'message' => 'name, email and password are required',
            ];
        }

        $user = new User();
        if ($user->registerUser($name, $email, $password))
        {
            $accessToken = AccessToken::generateAccessToken($user->id);

            if ($accessToken)
            {
                return [
                    'status' => 'success',
                    'accessToken' => $accessToken->accessToken,
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'failed to register',
            'errors' => $user->errors,
        ];
    }

    public function actionLogin(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $requestBody = Yii::$app->request->getRawBody();
        $request = json_decode($requestBody, true);

        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (!$email || !$password)
        {
            return [
                'status' => 'error',
                'message' => 'email and password are required',
            ];
        }

        $user = User::loginUser($email, $password);

        if ($user)
        {
            AccessToken::deleteAll(['userId' => $user->id]);
            $accessToken = AccessToken::generateAccessToken($user->id);

            if ($accessToken)
            {
                return [
                    'status' => 'success',
                    'accessToken' => $accessToken->accessToken,
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'email or password are incorrect',
            'errors' => $user->errors,
        ];
    }

    public function actionIndex(): string
    {
		Yii::$app->response->format = Response::FORMAT_HTML;
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            /*
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
            */
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

    public function actionUpdate($id): string
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save())
        {
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
        if (($model = User::findOne(['id' => $id])) !== null)
        {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
