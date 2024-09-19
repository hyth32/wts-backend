<?php

namespace backend\actions\post;

use Yii;
use yii\base\Action;
use yii\web\Response;

class GetPostsAction extends Action
{
    public $postService;

    public function run($userId = null): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $limit = Yii::$app->request->get('limit', 10); //default 10
        $offset = Yii::$app->request->get('offset', 0);

        $posts = $this->postService->getPosts($userId, $limit, $offset);

        if (empty($posts)) {
            return $this->controller->successResponse('No posts found');
        }

        $serializedPosts = $this->postService->serializePosts($posts);

        return $this->controller->successResponse('Posts found', $serializedPosts);
    }
}