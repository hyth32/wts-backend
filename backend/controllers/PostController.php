<?php

namespace backend\controllers;

use Yii;
use yii\rest\Controller;
use common\models\Post;
use common\models\AccessToken;
use common\models\ApiResponse;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\data\ActiveDataProvider;

class PostController extends Controller
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

		$accessToken = $request['accessToken'] ?? null;
		$text = $request['text'] ?? null;

		if (!$accessToken || !$text) {
			return ApiResponse::error('accessToken and text are required');
		}

		$tokenRecord = AccessToken::findOne(['accessToken' => $accessToken]);
		if (!$tokenRecord || !$tokenRecord->isTokenValid()) {
			return ApiResponse::error('invalid or expired accessToken');
		}

		$user = $tokenRecord->user;
		if (!$user) {
			return ApiResponse::error('user not found');
		}

		$post = new Post();
		if ($post->createPost($user->id, $request['text'])) {
			return ApiResponse::success('post created!');
		}

		return ApiResponse::error('failed to create post', $post->errors);
	}

	public function actionGetPosts(): array
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

	public function actionGetUserPosts($userId): array
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
}

