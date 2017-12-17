<?php

namespace bot;

class user_manager
{
    const USERS = "users.json";

    public function getUser($id)
    {
        if (file_exists(self::USERS)) {
            $users = json_decode(file_get_contents(self::USERS), true);
            if (isset($users[$id])) {
                return new user($id, $users[$id]["correct"], $users[$id]["incorrect"], $users[$id]["right_answer"]);
            }
            return null;
        }
    }

    public function saveUser(user $user) // Išsaugojame userius
    {
        if (file_exists(self::USERS))
        {
            $users = json_decode(file_get_contents(self::USERS),true);
        }

        $_user["correct"]=$user->getCorrectAnswers();
        $_user["incorrect"]=$user->getIncorrectAnswers();
        $_user["right_answer"]=$user->getRightAnswer();
        $users[$user->getId()]=$_user;

        $fp = fopen(self::USERS, 'w');
        fwrite($fp, json_encode($users));
        fclose($fp);
    }
}