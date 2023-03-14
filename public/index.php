<?php

require '../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\FormController;
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;

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
$homeController = new HomeController();
$formController = new FormController();


$router->get('/home',function() use ($twig, $homeController){
    $homeController->showHome($twig);
});

$router->mount('/home', function() use ($router,$twig, $homeController, $formController) {

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
    $postController->showPosts($twig);
});

$router->get('/post/(\d+)',function($postId) use ($twig, $postController){
    $postController->showPost($twig, $postId);
});

$router->run();
