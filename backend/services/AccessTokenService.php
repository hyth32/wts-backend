<?php

namespace backend\services;

use Yii;
use common\models\AccessToken;
use common\models\User;

class AccessTokenService
{
    public function generateAccessToken($userId): ?AccessToken
    {
        $accessToken = new AccessToken();
        $accessToken->userId = $userId;
        $accessToken->accessToken = Yii::$app->security->generateRandomString();
        $accessToken->expiresAt = time() + 3600;

        return $accessToken->save() ? $accessToken : null;
    }

    public function isTokenValid(AccessToken $accessToken): bool
    {
        return $accessToken->expiresAt >= time();
    }

    public function getUserFromToken($accessToken): ?User
    {
        $tokenRecord = AccessToken::findOne(['accessToken' => $accessToken]);
        if ($tokenRecord && $this->isTokenValid($tokenRecord)) {
            return $tokenRecord->user;
        }

        return null;
    }
}