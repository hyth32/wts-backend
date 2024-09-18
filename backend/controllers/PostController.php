<?php

namespace backend\controllers;

use Yii;
use yii\rest\Controller;
use common\models\Post;
use common\models\AccessToken;
use yii\web\Response;

class PostController extends Controller
{
    public function actionPublish()
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
}

