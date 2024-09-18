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
	/**
     * @inheritDoc
     */
    public function behaviors()
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

    public function actionCreate()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $requestBody = Yii::$app->request->getRawBody();
        $request = json_decode($requestBody, true);

        $name = $request['name'] ?? null;
        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (!$name || !$email || !$password) {
            return [
                'status' => 'error',
                'message' => 'name, email and password are required',
            ];
        }

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();

        if ($user->save()) {
            $accessToken = $this->generateAccessToken($user->id);

            if ($accessToken) {
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

    public function actionLogin()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $requestBody = Yii::$app->request->getRawBody();
        $request = json_decode($requestBody, true);

        $email = $request['email'] ?? null;
        $password = $request['password'] ?? null;

        if (!$email || !$password) {
            return [
                'status' => 'error',
                'message' => 'email and password are required',
            ];
        }

        $user = User::findOne(['email' => $email]);

        if (!$user || !$user->validatePassword($password)) {
            return [
                'status' => 'error',
                'message' => 'email or password are incorrect',
            ];
        }

        AccessToken::deleteAll(['userId' => $user->id]);

        $accessToken = $this->generateAccessToken($user->id);

        if ($accessToken) {
            return [
                'status' => 'success',
                'accessToken' => $accessToken->accessToken,
            ];
        }

        return [
            'status' => 'error',
            'message' => 'failed to login',
            'errors' => $user->errors,
        ];
    }

    protected function generateAccessToken($userId)
    {
        $accessToken = new AccessToken();
        $accessToken->userId = $userId;
        $accessToken->accessToken = Yii::$app->security->generateRandomString();
        $accessToken->expiresAt = time() + 3600; // 1 hour

        if ($accessToken->save()) {
            return $accessToken;
        }

        return null;
    }

	//CRUD
	/**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
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

/**
     * Displays a single User model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
		Yii::$app->response->format = Response::FORMAT_HTML;
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
//   ` public function actionCreate()
//    {
//        $model = new User();
//
//        if ($this->request->isPost) {
//            if ($model->load($this->request->post()) && $model->save()) {
//                return $this->redirect(['view', 'id' => $model->id]);
//            }
//        } else {
//            $model->loadDefaultValues();
//        }
//
//        return $this->render('create', [
//            'model' => $model,
//        ]);
//    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

/**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
