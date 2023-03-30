<?php

namespace App\Controllers;

class ErrorController
{
    public function showPage404($twig)
    {
        echo $twig->render('404.twig');
    }
}