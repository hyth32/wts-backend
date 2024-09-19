<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class AccessToken extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%accessTokens}}';
    }

    public function rules(): array
    {
        return [
            [['userId', 'accessToken', 'expiresAt'], 'required'],
            [['userId', 'expiresAt'], 'integer'],
            [['accessToken'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }
}
