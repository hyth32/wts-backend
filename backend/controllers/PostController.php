<?php

namespace backend\controllers;

use Yii;
use yii\rest\Controller;
use common\models\Post;
use common\models\AccessToken;
use yii\web\Response;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class PostController extends Controller
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
                        'delete' => ['POST'],
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

	$accessToken = $request['accessToken'] ?? null;
	$text = $request['text'] ?? null;

	if (!$accessToken || !$text) {
	    return [
		'status' => 'error',
		'message' => 'accessToken and text are required',
	    ];
	}

	$tokenRecord = AccessToken::findOne(['accessToken' => $accessToken]);
	if (!$tokenRecord || $tokenRecord->expiresAt < time()) {
	    return [
		'status' => 'error',
		'message' => 'invalid or expired accessToken',
	    ];
	}

	$user = $tokenRecord->user;
	if (!$user) {
	    return [
		'status' => 'error',
		'message' => 'user not found',
	    ];
	}

	$post = new Post();
	$post->userId = $user->id;
	$post->text = $request['text'];

	if ($post -> save()) {
	    return [
		'status' => 'success',
		'message' => 'post created!',
	    ];
	}

	return [
	    'success' => 'false',
	    'message' => 'failed to create post',
	    'errors' => $post->errors,
	];
    }

    public function actionGetPosts()
    {
		Yii::$app->response->format = Response::FORMAT_JSON;

		$limit = Yii::$app->request->get('limit', 10); //default 10
		$offset = Yii::$app->request->get('offset', 0);

		$posts = Post::find()
			->limit($limit)
			->offset($offset)
			->orderBy(['createdAt' => SORT_ASC])
			->all();

		if (empty($posts)) {
			return [
				'status' => 'success',
				'data' => [],
				'message' => 'no posts found',
	   		];
		}

		$serializedPosts = array_map(function ($post) {
	    	return [
				'id' => $post->id,
				'userId' => $post->userId,
				'text' => $post->text,
				'createdAt' => date('Y-m-d H:i:s', $post->createdAt),
	    	];
		}, $posts);

		return [
	    	'status' => 'success',
		    'data' => $serializedPosts,
		];
    }

	public function actionGetUserPosts($userId)
	{
		Yii::$app->response->format = Response::FORMAT_JSON;

		$limit = Yii::$app->request->get('limit', 10);
		$offset = Yii::$app->request->get('offset', 0);

		$posts = Post::find()
			->where(['userId' => $userId])
			->limit($limit)
			->offset($offset)
			->orderBy(['createdAt' => SORT_ASC])
			->all();

		if (empty($posts)) {
			return [
				'status' => 'success',
				'data' => [],
				'message' => 'no posts found',
			];
		}

		$serializedPosts = array_map(function ($post) {
			return [
				'id' => $post->id,
				'userId' => $post->userId,
				'text' => $post->text,
				'createdAt' => date('Y-m-d H:i:s', $post->createdAt),
			];
		}, $posts);

		return [
			'status' => 'success',
			'data' => $serializedPosts,
		];
	}

	/**
     * Lists all Post models.
     *
     * @return string
     */
	 public function actionIndex()
    {
		Yii::$app->response->format = Response::FORMAT_HTML;
        $dataProvider = new ActiveDataProvider([
            'query' => Post::find(),
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
     * Displays a single Post model.
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
     * Updates an existing Post model.
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
     * Deletes an existing Post model.
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
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

