<?php

namespace App\Controllers;

use \Twig\Environment as Twig;

class HomeController{

    public function showHome(Twig $twig): void{
        echo $twig->render('home.twig',['page' => "Phrase d'accroche"]);
    }
}