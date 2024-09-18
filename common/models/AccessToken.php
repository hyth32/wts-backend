<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class AccessToken extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%accessTokens}}';
    }

    public function rules()
    {
        return [
            [['userId', 'accessToken', 'expiresAt'], 'required'],
            [['userId', 'expiresAt'], 'integer'],
            [['accessToken'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    public static function generateAccessToken($userId)
    {
        $accessToken = new self();
        $accessToken->userId = $userId;
        $accessToken->accessToken = Yii::$app->security->generateRandomString();
        $accessToken->expiresAt = time() + 3600;

        if ($accessToken->save()) {
            return $accessToken;
        }

        return null;
    }

    public function isTokenValid()
    {
        return $this && $this->expiresAt >= time();
    }
}
