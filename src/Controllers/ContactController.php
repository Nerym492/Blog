<?php

namespace App\Controllers;

use \Twig\Environment as Twig;

class ContactController
{

    public function showContactForm(Twig $twig)
    {
        echo $twig->render('contact.twig', ['page' => 'Contact']);
    }

    public function sendMessage(Twig $twig)
    {
        
    }
}