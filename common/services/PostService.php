<?php

namespace common\services;

use common\models\Post;

class PostService
{
    private $errors = [];

    public function createPost($userId, $text): ?Post
    {
        $post = new Post();
        $post->userId = $userId;
        $post->text = $text;

        if ($post->save()) {
            return $post;
        }

        $this->errors[] = $post->errors;
        return null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}