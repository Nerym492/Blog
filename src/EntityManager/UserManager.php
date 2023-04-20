<?php

namespace App\EntityManager;

use App\Entity\User;

class UserManager extends Manager
{


    /**
     * Return the confirmation mail link
     * @param array $formRegister Contains all form data. Example : $formRegister['inputName']
     * @return string Link to confirm the mail
     */
    public function createUser(array $formRegister): string
    {
        $fullName = explode(" ", $formRegister['fullName']);
        $lastName = $fullName[0];
        $firstName = $fullName[1];

        // Creating a hash for the password.
        $formRegister['password'] = password_hash($formRegister['password'], PASSWORD_BCRYPT);

        // Generates a code that will be used in the confirmation link sent by email.
        $verificationCode = bin2hex(openssl_random_pseudo_bytes(20));

        $statement = $this->database->prepare(
            "INSERT INTO ".$this->env->getVar('DB_NAME').".user
                (mail, pseudo, last_name, first_name, password, verification_code, confirmed_mail, user_type_id)
                VALUES(:mail, :pseudo, :last_name, :first_name, :password, 
                       :verification_code, :confirmed_mail, :user_type_id);"
        );

        $statement->execute(
            [
             ':mail'              => $formRegister['mail'],
             ':pseudo'            => $formRegister['pseudo'],
             ':last_name'         => $lastName,
             ':first_name'        => $firstName,
             ':password'          => $formRegister['password'],
             ':verification_code' => $verificationCode,
             ':confirmed_mail'    => 0,
             ':user_type_id'      => 3,
            ]
        );

        $rootUrl = $this->env->getVar('ROOT_URL');
        $publicPath = $this->env->getVar('PUBLIC_PATH');

        return $rootUrl.$publicPath."/logIn/mail/".$formRegister['mail']."/verificationCode/".$verificationCode;

    }//end createUser()


    /**
     * Confirm the user's Mail
     * @param string $mail             Mail which needs to be verified
     * @param string $verificationCode Code associated with the mail to confirm it
     * @return array
     * $array['message'] -> status of the mail confirmation,
     * $array['messageClass'] CSS class of the message
     */
    public function confirmMail(string $mail, string $verificationCode): void
    {
        $statement = $this->database->prepare(
            "UPDATE ".$this->env->getVar('DB_NAME').".`user`
                   SET confirmed_mail=:confirmed_mail, verification_code=:reset_verification_code
                   WHERE mail=:mail AND verification_code=:verification_code
            "
        );

        // Verification code reset --> mail can only be confirmed once.
        $statement->execute(
            [
             ':confirmed_mail'          => 1,
             ':verification_code'       => $verificationCode,
             ':reset_verification_code' => '',
             'mail'                     => $mail,
            ]
        );

        if (($statement->rowCount() === 1)) {
            $this->session->set('message', 'Your mail has been successfully confirmed !');
            $this->session->set('messageClass', 'success');
        }

        if (($statement->rowCount() !== 1)) {
            $this->session->set('message', 'This confirmation link si no longer valid.');
            $this->session->set('messageClass', 'danger');
        }

    }//end confirmMail()


    /**
     * Return a user object from the database
     *
     * @param int    $userId User ID
     * @param string $mail   Mail of the user
     * @return User|null
     */
    public function getUser(int $userId=0, string $mail=""): ?User
    {
        $user = null;

        $statement = $this->database->prepare(
            "SELECT user_id, mail, pseudo, last_name, first_name, password, user_type_id
             FROM user
             WHERE user_id = :user_id
             OR mail = :mail"
        );

        $statement->execute([':user_id' => $userId, ':mail' => $mail]);
        $row = $statement->fetch();

        if ($row !== false) {
            // A user has been found.
            $user = new User();
            $user->setUserId($row['user_id']);
            $user->setMail($row['mail']);
            $user->setPseudo($row['pseudo']);
            $user->setLastName($row['last_name']);
            $user->setFirstName($row['first_name']);
            $user->setUserTypeId($row['user_type_id']);
        }

        return $user;

    }//end getUser()


    /**
     * Check if the data is already used by another user
     *
     * @param string $field Name of the field in the database
     * @param string $data  Data of the field we want to check
     * @return array Keys available -> alreadyExists, formIsValid
     */
    public function checkDataAlreadyExists(string $field, string $data): array
    {
        $statement = $this->database->prepare(
            "SELECT count(user_id) as 'nbLines'
                   FROM user
                   WHERE ".$field."=:data"
        );

        $statement->execute([':data' => $data]);
        $result = $statement->fetch();

        if ($result['nbLines'] === 1) {
            $this->session->set('message', 'This '.$field.' is already used !');
            $this->session->set('messageClass', 'danger');
            return [
                    'alreadyExists' => true,
                    'formIsValid'   => false,
                   ];
        }

        return [
                'alreadyExists' => false,
                'formIsValid'   => true,
               ];

    }//end checkDataAlreadyExists()


    /**
     * Check the login of the user
     *
     * @param string $mail     User's mail
     * @param string $password User's password
     * @return bool
     */
    public function checkLogin(string $mail, string $password): bool
    {
        $statement = $this->database->prepare(
            "SELECT password as 'password_hash'
                   FROM user
                   WHERE mail=:mail"
        );

        $statement->execute([':mail' => $mail]);

        $row = $statement->fetch();

        // Check if row is empty and check the password hash (True or false).
        return ($row && password_verify($password, $row['password_hash']));

    }//end checkLogin()


    /**
     * Connect a user and assigns his data to the session
     *
     * @param string $mail Mail of the user
     * @return void
     */
    public function connectUser(string $mail): void
    {
        $user = $this->getUser(mail: $mail);

        if ($user !== null) {
            $this->session->set('user_id', $user->getUserId());
            $this->session->set('mail', $mail);
            $this->session->set('first_name', $user->getFirstName());
            $this->session->set('last_name', $user->getLastName());
            $this->session->set('pseudo', $user->getPseudo());
            $this->session->set('isAdmin', $user->isAdmin());
        }

    }//end connectUser()


    /**
     * Disconnect the user
     *
     * @return void
     */
    public function disconnectUser(): void
    {
        $this->session->destroy();

    }//end disconnectUser()


}//end class
