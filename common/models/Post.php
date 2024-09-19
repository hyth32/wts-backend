<?php

namespace common\models;

use yii\db\ActiveRecord;
use yii\db\ActiveQuery;

class Post extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%posts}}';
    }

    public function rules(): array
    {
        return [
            [['text'], 'required'],
            [['userId'], 'integer'],
            [['text'], 'string'],
            ['createdAt', 'safe'],
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    public function beforeSave($insert): bool
    {
        if ($insert) {
            $this->createdAt = time();
        }
        return parent::beforeSave($insert);
    }

    public function createPost($userId, $text): ?self
    {
        $this->userId = $userId;
        $this->text = $text;

        if ($this->save()) {
            return $this;
        }

        return null;
    }
}
