<?php

namespace App\EntityManager;

use App\Entity\User;
use App\Lib\DatabaseConnection;

class UserManager
{
    /**
     * Return the confirmation mail link
     * @param array $formRegister Contains all form data. Example : $formRegister['inputName']
     * @return string Link to confirm the mail
     */
    public function createUser(array $formRegister): string
    {
        $connexion = new DatabaseConnection();

        $fullName = explode(" ", $formRegister['fullName']);
        $lastName = $fullName[0];
        $firstName = $fullName[1];

        //Creating a hash for the password
        $formRegister['password'] = password_hash($formRegister['password'], PASSWORD_DEFAULT);

        //Generates a code that will be used in the confirmation link sent by email
        $verificationCode = bin2hex(openssl_random_pseudo_bytes(20));

        $statement = $connexion->getConnection()->prepare(
            "INSERT INTO blog.`user`
                (mail, pseudo, last_name, first_name, password, verification_code, confirmed_mail, user_type_id)
                VALUES(:mail, :pseudo, :last_name, :first_name, :password, 
                       :verification_code, :confirmed_mail, :user_type_id);"
        );

        $statement->execute([
            ':mail' => $formRegister['mail'],
            ':pseudo' => $formRegister['pseudo'],
            ':last_name' => $lastName,
            ':first_name' => $firstName,
            ':password' => $formRegister['password'],
            ':verification_code' => $verificationCode,
            ':confirmed_mail' => 0,
            ':user_type_id' => 3
        ]);

        return "http://localhost/blog/public/register/" . $formRegister['mail'] . "/" . $verificationCode;
    }

    /**
     * Confirm the user's Mail
     * @param string $mail Mail which needs to be verified
     * @param string $verificationCode Code associated with the mail to confirm it
     * @return array
     * $array['message'] -> status of the mail confirmation,
     * $array['messageClass'] CSS class of the message
     */
    public function confirmMail(string $mail, string $verificationCode): array
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "UPDATE blog.`user`
                   SET confirmed_mail=:confirmed_mail, verification_code=:reset_verification_code
                   WHERE mail=:mail AND verification_code=:verification_code
            ");

        //Verification code reset --> mail can only be confirmed once
        $statement->execute([
            ':confirmed_mail' => 1,
            ':verification_code' => $verificationCode,
            ':reset_verification_code' => '',
            'mail' => $mail
        ]);

        if (($statement->rowCount() == 1)) {
            $message = "Your mail has been successfully confirmed !";
            $messageClass = "success";
        } else {
            $message = "This confirmation link si no longer valid.";
            $messageClass = "danger";
        }

        return ['message' => $message, 'messageClass' => $messageClass];
    }

    public function getUser(int $userId): User
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "SELECT mail, pseudo, last_name, first_name, password, user_type_id
             FROM user
             WHERE user_id = :user_id"
        );

        $statement->execute([':user_id' => $userId]);
        $row = $statement->fetch();

        $user = new User();

        if ($row) {
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

    /**
     * Check if data is already used by another user
     * @param string $field Name of the field in the database
     * @param string $data Data we want to check
     * @return bool
     */
    public function checkDataAlreadyExists(string $field, string $data): bool
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "SELECT count(user_id) as 'nbLines'
                   FROM user
                   WHERE " . $field . "=:data"
        );

        $statement->execute([':data' => $data]);
        $result = $statement->fetch();

        return (int)$result['nbLines'] !== 0;
    }
}