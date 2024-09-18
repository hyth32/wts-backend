<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $passwordHash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $authKey
 * @property integer $status
 * @property string $password write-only password
 */
class User extends ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
    	return [
            [['name', 'email', 'passwordHash', 'authKey'], 'required'],
            ['email', 'email'],
            ['email', 'unique'],
            [['name', 'passwordHash', 'authKey'], 'string', 'max' => 255],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
       // throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
	    return static::findOne(['accessToken' => $token]);
    }

    public function getId()
    {
        return $this->id();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
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

        if ($this->save()) {
            return $this;
        }

        return null;
    }

    public static function loginUser($email, $password)
    {
        $user = static::findOne(['email' => $email]);

        if ($user && $user->validatePassword($password)) {
            return $user;
        }

        return null;
    }
}
