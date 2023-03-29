<?php

namespace App\Controllers;

use \Twig\Environment as Twig;

class AdminController
{
    public function showAdminPanel(Twig $twig): void
    {
        echo $twig->render('adminPanel.twig', [
            'page' => 'Administration'
        ]);
    }
}