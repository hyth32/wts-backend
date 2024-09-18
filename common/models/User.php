<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
    	return [
            [['name', 'email', 'passwordHash', 'authKey'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            [['name', 'passwordHash', 'authKey'], 'string', 'max' => 255],
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
       // throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	    return static::findOne(['accessToken' => $token]);
    }

    public function getId()
    {
        return $this->id();
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    public function setPassword($password)
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    public function generateAuthKey()
    {
        $this->authKey = Yii::$app->security->generateRandomString();
    }

    public function getAccessTokens()
    {
    	return $this->hasMany(AccessToken::class, ['userId' => 'id']);
    }

    public function registerUser($name, $email, $password)
    {
        $this->name = $name;
        $this->email = $email;
        $this->setPassword($password);
        $this->generateAuthKey();

        if ($this->save())
        {
            return $this;
        }

        return null;
    }

    public static function loginUser($email, $password)
    {
        $user = static::findOne(['email' => $email]);

        if ($user && $user->validatePassword($password))
        {
            return $user;
        }

        return null;
    }
}
