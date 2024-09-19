<?php

namespace common\models;

class ApiResponse
{
    public static function success($data = [], $message = "OK"): array
    {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];
    }

    public static function error($message = 'Error', $errors = []): array
    {
        return [
            'status' => 'error',
            'message' => $message,
            'errors' => $errors,
        ];
    }
}
