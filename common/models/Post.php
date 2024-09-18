<?php

namespace common\models;

use Yii;
use yii\db\ActiveRecord;

class Post extends ActiveRecord
{
    public static function tableName()
    {
	    return '{{%posts}}';
    }

    public function rules()
    {
    	return [
    	    [['text'], 'required'],
    	    [['userId'], 'integer'],
    	    [['text'], 'string'],
    	    ['createdAt', 'safe'],
    	];
    }

    public function getUser()
    {
    	return $this->hasOne(User::class, ['id' => 'userId']);
    }

    public function beforeSave($insert)
    {
    	if ($insert)
        {
    	    $this->createdAt = time();
    	}
    	return parent::beforeSave($insert);
    }

    public function createPost($userId, $text)
    {
        $this->userId = $userId;
        $this->text = $text;

        if ($this->save())
        {
            return $this;
        }

        return null;
    }
}
