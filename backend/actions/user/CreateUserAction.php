<?php

namespace backend\actions\user;

use Yii;
use yii\base\Action;
use yii\web\Response;

class CreateUserAction extends Action
{
    public $userService;
    public $accessTokenService;

    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = $this->controller->getRequestBody();

        if ($this->controller->validateCreateRequest($request)) {
            $user = $this->userService->createUser($request['name'], $request['email'], $request['password']);

            if ($user) {
                $accessToken = $this->accessTokenService->generateAccessToken($user->getId());
                return $this->controller->successResponse('User registered', $accessToken->accessToken);
            }

            return $this->controller->errorResponse('Failed to register', $this->userService->getErrors());
        }

        return $this->controller->errorResponse('Name, email and password are required');
    }
}