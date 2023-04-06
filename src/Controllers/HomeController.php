<?php

namespace App\Controllers;

use \Twig\Environment as Twig;

class HomeController extends AbstractController
{

    public function showHome(Twig $twig): void{
        print_r($this->renderView('home.twig',['page' => "Phrase d'accroche"]));
    }
}