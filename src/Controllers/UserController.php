<?php

namespace App\Controllers;

use \Twig\Environment as Twig;
use App\EntityManager\UserManager;

class UserController
{
    public function confirmMailAddress(Twig $twig, string $mail, string $verificationCode): void
    {
        $userManager = new UserManager();
        $mailConfirmation = $userManager->confirmMail($mail, $verificationCode);

        echo $twig->render('logIn.twig',[
            'message' => $mailConfirmation['message'],
            'messageClass' => $mailConfirmation['messageClass']
        ]);
    }

    public function logOut(Twig $twig): void
    {
        $userManager = new UserManager();
        $userManager->disconnectUser();
        echo $twig->render('home.twig',['session' => '']);
    }


}