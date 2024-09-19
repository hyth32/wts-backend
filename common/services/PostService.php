<?php

namespace common\services;

use common\models\Post;

class PostService
{
    public function createPost($userId, $text): ?Post
    {
        $post = new Post();
        $post->userId = $userId;
        $post->text = $text;

        return $post->save() ? $post : null;
    }
}