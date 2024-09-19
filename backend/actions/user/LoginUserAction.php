<?php

namespace backend\actions\user;

use Yii;
use yii\base\Action;
use yii\web\Response;

class LoginUserAction extends Action
{
    public $userService;
    public $accessTokenService;

    public function run(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $request = $this->controller->getRequestBody();

        if ($this->controller->validateLoginRequest($request)) {
            $user = $this->userService->loginUser($request['email'], $request['password']);

            if ($user) {
                $accessToken = $this->accessTokenService->generateAccessToken($user->getId());
                return $this->controller->successResponse('Login successful', $accessToken->accessToken);
            }

            return $this->controller->errorResponse('Email or password are incorrect');
        }

        return $this->controller->errorResponse('Email and password are required');
    }
}