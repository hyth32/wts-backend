<?php

namespace common\services;

use common\models\User;
use common\models\AccessToken;

class UserService
{
    public function createUser($name, $email, $password, $role = User::ROLE_USER): ?User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->role = $role;

        return $user->save() ? $user : null;
    }

    public function loginUser($email, $password): ?User
    {
        $user = User::findByEmail($email);
        if ($user && $user->validatePassword($password)) {
            AccessToken::deleteAll(['userId' => $user->id]);
            return $user;
        }

        return null;
    }
}