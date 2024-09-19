<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\ActiveQuery;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';

    public static function tableName(): string
    {
        return '{{%user}}';
    }

    public function rules(): array
    {
        return [
            [['name', 'email', 'passwordHash', 'authKey', 'role'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            [['name', 'passwordHash', 'authKey', 'role'], 'string', 'max' => 255],
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public static function findIdentity($id): ?self
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?self
    {
        return static::findOne(['accessToken' => $token]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthKey(): string
    {
        return $this->authKey;
    }

    public function validateAuthKey($authKey): bool
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    public function setPassword($password): void
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey(): void
    {
        $this->authKey = Yii::$app->security->generateRandomString();
    }

    public function getAccessTokens(): ActiveQuery
    {
        return $this->hasMany(AccessToken::class, ['userId' => 'id']);
    }

    public static function findByEmail($email): ?self
    {
        return static::findOne(['email' => $email]);
    }
}
