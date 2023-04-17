<?php

namespace App\Controllers;

use App\EntityManager\UserManager;

/**
 * User Controller, used when the user log in and log out
 */
class UserController extends AbstractController
{


    /**
     * Confirm mail when the user click on the link he received on his mail address
     *
     * @param string $mail             Mail to confirm.
     * @param string $verificationCode Verification code.
     *
     * @return void
     */
    public function confirmMailAddress(string $mail, string $verificationCode): void
    {
        $this->userManager->confirmMail($mail, $verificationCode);

        $this->renderView('logIn.twig');

    }//end confirmMailAddress()


    /**
     * Disconnect the user by clearing the session variable
     *
     * @return void
     */
    public function logOut(): void
    {
        $this->userManager->disconnectUser();
        $this->renderView('home.twig', ['session' => '']);

    }//end logOut()


}//end class
