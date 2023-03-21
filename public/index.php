<?php

require '../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\FormController;
use App\Controllers\UserController;
use Dotenv\Dotenv;

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
//var_dump(get_included_files());

$router = new \Bramus\Router\Router();

$postController = new PostController();
$formController = new FormController();
$userController = new UserController();

$router->mount('/home', function() use ($router,$twig, $formController) {
    $homeController = new HomeController();

    //The page is displayed without sending the form
    $router->get('/',function() use ($twig, $homeController){
        $homeController->showHome($twig);
    });

    //The vistor has sent the form
    $router->post('/', function() use ($twig, $formController){
        $formController->checkContactForm($twig);
    });

});

$router->get('/posts',function() use ($twig, $postController){
    //Displays all posts
    $postController->showPosts($twig);
});

$router->get('/post/(\d+)',function($postId) use ($twig, $postController){
    //Displays a single post
    $postController->showPost($twig, $postId);
});

$router->mount('/register', function() use ($router, $twig, $formController){
    //Displays the register form
    $router->get('/',function() use ($twig, $formController){
        $formController->showRegisterForm($twig);
    });
    
    //The visitor has sent the register form
    $router->post('/', function() use ($twig, $formController){
        $formController->checkRegisterForm($twig);
    });
});

$router->mount('/logIn', function() use ($router, $twig, $formController, $userController){
    //Displays the login form
    $router->get('/',function() use ($twig, $formController){
        $formController->showLogInForm($twig);
    });

    //The user clicked on the link he received by mail
    $router->get('/mail/([^/<>]+)/verificationCode/(\w+)', function($mail, $verificationCode) use ($twig, $userController){
        $userController->confirmMailAddress($twig, $mail, $verificationCode);
    });
});



$router->run();
