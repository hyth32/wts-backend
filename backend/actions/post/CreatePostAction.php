<?php

namespace backend\actions\post;

use Yii;
use yii\base\Action;
use yii\web\Response;

class CreatePostAction extends Action
{
    public $postService;
    public $accessTokenService;

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = $this->controller->getRequestBody();

        if ($this->controller->validatePostRequest($request)) {
            $user = $this->accessTokenService->getUserFromToken($request['accessToken']);

            if ($user) {
                $post = $this->postService->createPost($user->id, $request['text']);

                if ($post) {
                    return $this->controller->successResponse('Post created');
                }

                return $this->controller->errorResponse('Failed to create post', $this->postService->getErrors());
            }

            return $this->controller->errorResponse('User not found');
        }

        return $this->controller->errorResponse('AccessToken and text are required');
    }
}