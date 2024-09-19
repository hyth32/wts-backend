<?php

namespace backend\controllers;

use Yii;
use common\models\Post;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;
use common\services\PostService;
use common\services\AccessTokenService;

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

	public function actionCreate(): array
	{
		Yii::$app->response->format = Response::FORMAT_JSON;

		$request = $this->getRequestBody();

		if ($this->validatePostRequest($request)) {
			$user = $this->accessTokenService->getUserFromToken($request['accessToken']);

			if ($user) {
				$post = $this->postService->createPost($user->id, $request['text']);

				if ($post) {
					return $this->successResponse('post created!');
				}

				return $this->errorResponse('failed to create post', $post->errors);
			}

			return $this->errorResponse('user not found');
		}

		return $this->errorResponse('accessToken and text are required');
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
			return $this->successResponse('no posts found');
		}

		$serializedPosts = array_map(function ($post) {
			return [
				'id' => $post->id,
				'userId' => $post->userId,
				'text' => $post->text,
				'createdAt' => date('Y-m-d H:i:s', $post->createdAt),
			];
		}, $posts);

		return $this->successResponse($serializedPosts);
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

