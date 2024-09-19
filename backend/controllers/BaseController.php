<?php

namespace backend\controllers;

use Yii;
use yii\rest\Controller;
use common\models\ApiResponse;

class BaseController extends Controller
{
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            Yii::$app->params['controllerBehaviors'],
        );
    }

    public function getRequestBody(): array
    {
        $requestBody = Yii::$app->request->getRawBody();
        return json_decode($requestBody, true) ?? [];
    }

    public function successResponse($message, $data = []): array
    {
        return ApiResponse::success($message, $data);
    }

    public function errorResponse($message, $errors = []): array
    {
        return ApiResponse::error($message, $errors);
    }
}