<?php

namespace backend\controllers;

use Yii;
use common\models\Post;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use backend\services\PostService;
use backend\services\AccessTokenService;

class PostController extends BaseController
{
	private $postService;
	private $accessTokenService;

	public function __construct($id, $module, PostService $postService, AccessTokenService $accessTokenService, $config = [])
	{
		$this->postService = $postService;
		$this->accessTokenService = $accessTokenService;
		parent::__construct($id, $module, $config);
	}

	public function actions()
	{
		return [
			'create' => [
				'class' => 'backend\actions\post\CreatePostAction',
				'postService' => $this->postService,
				'accessTokenService' => $this->accessTokenService,
			],
			'get-posts' => [
				'class' => 'backend\actions\post\GetPostsAction',
				'postService' => $this->postService,
			],
		];
	}

	public function actionIndex(): string
	{
		Yii::$app->response->format = Response::FORMAT_HTML;

		$dataProvider = new ActiveDataProvider([
			'query' => Post::find(),
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

	protected function findModel($id): Post
	{
		if (($model = Post::findOne(['id' => $id])) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}

	private function validatePostRequest($request): bool
	{
		return !empty($request['accessToken']) && !empty($request['text']);
	}
}

