<?php

namespace backend\controllers;

use Yii;
use yii\rest\Controller;
use common\models\Post;
use common\models\User;
use common\models\AccessToken;
use common\models\ApiResponse;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use common\services\PostService;
use common\services\AccessTokenService;

class PostController extends Controller
{
	private $postService;
	private $accessTokenService;

	public function __construct($id, $module, PostService $postService, AccessTokenService $accessTokenService, $config = [])
	{
		$this->postService = $postService;
		$this->accessTokenService = $accessTokenService;
		parent::__construct($id, $module, $config);
	}

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

		if ($this->validatePostRequest($request)) {
			$user = $this->accessTokenService->getUserFromToken($request['accessToken']);

			if ($user) {
				$post = $this->postService->createPost($user->id, $request['text']);

				if ($post) {
					return ApiResponse::success('post created!');
				}

				return ApiResponse::error('failed to create post', $post->errors);
			}

			return ApiResponse::error('user not found');
		}

		return ApiResponse::error('accessToken and text are required');
	}

	public function actionGetPosts($userId = null): array
	{
		Yii::$app->response->format = Response::FORMAT_JSON;

		$limit = Yii::$app->request->get('limit', 10); //default 10
		$offset = Yii::$app->request->get('offset', 0);

		$query = Post::find()->limit($limit)->offset($offset)->orderBy(['createdAt' => SORT_ASC]);

		if ($userId) {
			$query->where(['userId' => $userId]);
		}

		$posts = $query->all();

		if (empty($posts)) {
			return ApiResponse::success('no posts found');
		}

		$serializedPosts = array_map(function ($post) {
			return [
				'id' => $post->id,
				'userId' => $post->userId,
				'text' => $post->text,
				'createdAt' => date('Y-m-d H:i:s', $post->createdAt),
			];
		}, $posts);

		return ApiResponse::success($serializedPosts);
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

