<?php

namespace App\Controllers;

use App\EntityManager\CommentManager;
use App\EntityManager\PostManager;
use App\EntityManager\UserManager;
use App\Lib\Environment;
use App\Lib\Session;
use Dotenv\Dotenv;
use Pagination\Pagination;
use Pagination\StrategySimple;
use Throwable;
use Twig\Environment as Twig;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

/**
 * Main controller.
 */
abstract class AbstractController
{

    /**
     * @var PostManager Posts data management.
     */
    protected PostManager $postManager;

    /**
     * @var CommentManager Comments data management.
     */
    protected CommentManager $commentManager;

    /**
     * @var UserManager Users data management.
     */
    protected UserManager $userManager;

    /**
     * Twig Environment object used to render twig templates.
     *
     * @var Twig
     */
    protected Twig $twig;

    /**
     * Session object to avoid direct super global uses.
     *
     * @var Session
     */
    protected Session $session;

    /**
     * Twig FilesystemLoader use to manage twig files.
     *
     * @var FilesystemLoader
     */
    private FilesystemLoader $twigLoader;

    /**
     * Environment variables.
     *
     * @var Environment
     */
    protected Environment $env;


    /**
     * Instantiation of the objects
     */
    public function __construct()
    {
        $this->session        = new Session();
        $this->twigLoader     = new FilesystemLoader('../Templates');

        $this->twig = new Twig(
            $this->twigLoader,
            [
             'debug' => true,
             'cache' => '../tmp',
            ]
        );
        $this->twig->addExtension(new DebugExtension());
        $this->setTwigSessionGlobals();
        $this->env = new Environment();
        $this->postManager    = new PostManager($this->session, $this->env);
        $this->commentManager = new CommentManager($this->session, $this->env);
        $this->userManager = new UserManager($this->session, $this->env);

    }//end __construct()


    /**
     * Render the twig template.
     * If something goes wrong while the page is loading, display an error.
     *
     * @param string $twigFile Name of the twig file.
     * @param array  $params   List of parameters used by the templates.
     *
     * @return void
     */
    public function renderView(string $twigFile, array $params=[]): void
    {
        try {
            $render = $this->twig->render($twigFile, $params);
        } catch (Throwable) {
            $render = '';
            $this->session->set('message', "Une erreur s'est produite pendant le chargement de la page");
            $this->session->set('messageClass', 'danger');
        }

        echo $render;

    }//end renderView()


    /**
     * Redirect the page to the specified url
     *
     * @param string $url Url target
     * @return void
     */
    public function redirectTo(string $url): void
    {
        header('Location: '.$url, true, 303);

    }//end redirectTo()


    /**
     * Add globals variables to the twig object.
     *
     * @return void
     */
    public function setTwigSessionGlobals(): void
    {
        $this->twig->addGlobal('session', $this->session->get());

    }//end setTwigSessionGlobals()


    /**
     * Get the session variable
     *
     * @return Session
     */
    public function getSession(): Session
    {
        return $this->session;

    }//end getSession()


    /**
     * Gets all the variables needed to build the pagination
     *
     * @param integer $nbRows       Total number of rows given by the query.
     * @param integer $limitPerPage Number of lines per page.
     * @param integer $activePage   Active page when the pagination is loaded.
     *
     * @return array Contains all the elements of the pagination (firstPage, lastPage, previousPage, nextPage,
     * activePage,* iterator).
     */
    protected function getPagination(int $nbRows, int $limitPerPage, int $activePage): array
    {
        $pagesNumbers = [
                         'firstPage'    => 1,
                         'lastPage'     => 1,
                         'previousPage' => 1,
                         'nextPage'     => 1,
                         'activePage'   => 1,
                         'iterator'     => 1,
                        ];

        // Use pagination class with results, per page and page.
        if ($nbRows > 0) {
            $pagination = new Pagination($nbRows, $limitPerPage, $activePage);
            // Get indexes in page.
            // StrategySimple(param = number of pages visible in the pagination).
            $numberOfPages = new StrategySimple(5);
            $indexes       = $pagination->getIndexes($numberOfPages);
            $pagesNumbers  = [
                              'firstPage'    => $pagination->getFirstPage(),
                              'lastPage'     => $pagination->getLastPage(),
                              'previousPage' => $pagination->getPreviousPage(),
                              'nextPage'     => $pagination->getNextPage(),
                              'activePage'   => $pagination->getPage(),
                              'iterator'     => $indexes->getIterator(),
                             ];
        }//end if

        return $pagesNumbers;

    }//end getPagination()


}//end class
