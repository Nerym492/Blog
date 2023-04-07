<?php

namespace App\Controllers;

class HomeController extends AbstractController
{


    /**
     * Display the home page
     *
     * @return void
     */
    public function showHome(): void
    {
        $this->renderView('home.twig', ['page' => "Phrase d'accroche"]);

    }//end showHome()


}//end class
