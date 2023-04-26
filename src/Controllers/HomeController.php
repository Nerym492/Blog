<?php

namespace App\Controllers;

use Exception;

class HomeController extends AbstractController
{


    /**
     * Display the home page
     *
     * @return void
     * @throws Exception
     */
    public function showHome(): void
    {
        $this->generateToken();
        $this->renderView('home.twig', ['page' => "Phrase d'accroche"]);

    }//end showHome()


}//end class
