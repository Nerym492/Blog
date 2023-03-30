<?php

require '../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\FormController;
use App\Controllers\UserController;
use App\Controllers\AdminController;
use Dotenv\Dotenv;


session_start();
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
$dotenv->required('DB_PASS');


$loader = new \Twig\Loader\FilesystemLoader('../Templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
    'cache' => '../tmp',
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addGlobal('session', $_SESSION);

$router = new \Bramus\Router\Router();

$formController = new FormController();
$userController = new UserController();
$adminController = new AdminController();

$router->mount('/home', function () use ($router, $twig, $formController, $userController) {
    $homeController = new HomeController();

    //The page is displayed without sending the form
    $router->get('/', function () use ($twig, $homeController) {
        $homeController->showHome($twig);
    });

    //The visitor has sent the form
    $router->post('/', function () use ($twig, $formController) {
        $formController->checkContactForm($twig);
    });

    $router->get('/loggedOut', function () use ($twig, $userController) {
        $userController->logOut($twig);
    });

});

$router->mount('/posts', function () use ($router, $twig, $formController) {
    $postController = new PostController();

    //Displays all the posts
    $router->get('/', function () use ($twig, $postController) {
        $postController->showPostsPage($twig);
    });
    //Posts reload with Ajax
    $router->get('/posts-page-(\d+)', function ($pageNum) use ($twig, $postController) {
        $postController->showPostsWidget($twig, $pageNum);
    });

    //Displays a single post
    $router->get('/(\d+)', function ($postId) use ($twig, $postController) {
        $postController->showPost($twig, $postId);
    });

    //The comment form has been submitted
    $router->post('/(\d+)', function ($postId) use ($twig, $formController) {
        if (!empty($_POST['comment'])) {
            $formController->checkCommentForm($postId);
            header('Location: /blog/public/posts/' . $postId . '#comments-box-post', true, 303);
            /*After processing the data, we redirect the browser to the same page with the HTTP status code 303
            The old header is replaced with a new one that does not contain $_POST data
            Post/Redirect/Get
            Prevent the form from being submitted multiple times by refreshing the page*/
        }
    });

    //Displays the post form
    $router->get('/create', function () use ($twig, $formController) {
        $formController->showPostForm($twig);
    });

    //The post form has been submitted
    $router->post('/create', function () use ($twig, $formController) {
        $formController->checkPostForm($twig);
    });
});


$router->mount('/register', function () use ($router, $twig, $formController) {
    //Displays the register form
    $router->get('/', function () use ($twig, $formController) {
        $formController->showRegisterForm($twig);
    });

    //The visitor has sent the register form
    $router->post('/', function () use ($twig, $formController) {
        $formController->checkRegisterForm($twig);
    });
});

$router->mount('/logIn', function () use ($router, $twig, $formController, $userController) {
    //Displays the login form
    $router->get('/', function () use ($twig, $formController) {
        $formController->showLogInForm($twig);
    });

    //The user clicked on the link he received by mail
    $router->get('/mail/([^/<>]+)/verificationCode/(\w+)', function ($mail, $verificationCode) use ($twig, $userController) {
        $userController->confirmMailAddress($twig, $mail, $verificationCode);
    });

    //The user has submitted the login form
    $router->post('/', function () use ($twig, $formController) {
        $formController->checkLogInForm($twig);
    });
});

if (isset($_SESSION['isAdmin']) and ($_SESSION['isAdmin'])){
    $router->mount('/administration', function () use ($router, $twig, $adminController) {

        $router->get('/', function () use ($twig, $adminController) {
            $adminController->showAdminPanel($twig);
        });

        $router->get('/posts-page-(\d+)', function ($pageNum) use ($twig, $adminController) {
            $adminController->reloadPostsList($twig, $pageNum);
        });

    });
}

$router->run();

//var_dump($_SERVER['REQUEST_METHOD']);
//var_dump($_POST);
//var_dump($_SESSION);