<?php

namespace App\Controllers;

class ErrorController extends AbstractController
{


    /**
     * Display page 404
     *
     * @return void
     */
    public function showPage404(): void
    {
        $this->renderView('404.twig');

    }//end showPage404()


}//end class
