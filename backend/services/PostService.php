<?php

namespace backend\services;

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

    public function getPosts($userId = null, $limit = 10, $offset = 0): array
    {
        $query = Post::find()->limit($limit)->offset($offset)->orderBy(['createdAt' => SORT_ASC]);

        if ($userId) {
            $query->where(['userId' => $userId]);
        }

        return $query->all();
    }

    public function serializePosts($posts): array
    {
        return array_map(function ($post) {
            return [
                'id' => $post->id,
                'userId' => $post->userId,
                'text' => $post->text,
                'createdAt' => date('Y-m-d H:i:s', $post->createdAt),
            ];
        }, $posts);
    }
}