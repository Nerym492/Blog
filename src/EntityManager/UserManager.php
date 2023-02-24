<?php

namespace App\EntityManager;

use App\Entity\User;
use App\Lib\DatabaseConnection;

class UserManager
{
    public function getUser(int $userId): User
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "SELECT mail, pseudo, last_name, first_name, password, user_type_id
             FROM user
             WHERE user_id = :user_id"
        );

        $statement->execute();
        $row = $statement->fetch();

        $user = new User();

        if ($row){
            $user->setUserId($userId);
            $user->setMail($row['mail']);
            $user->setPseudo($row['pseudo']);
            $user->setLastName($row['last_name']);
            $user->setFirstName($row['first_name']);
            $user->setPassword($row['password']);
            $user->setUserId($row['user_type_id']);
        }

        return $user;
        
    }
}