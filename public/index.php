<?php

require '../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\ContactController;
use Dotenv\Dotenv;
use PHPMailer\PHPMailer\PHPMailer;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'])->notEmpty();

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
$contactController = new ContactController();
$mail = new PHPMailer(true);


$router->get('/home',function() use ($twig, $homeController){
    $homeController->showHome($twig);
});

$router->get('/posts',function() use ($twig, $postController){
    $postController->showPosts($twig);
});

$router->get('/post/(\d+)',function($postId) use ($twig, $postController){
    $postController->showPost($twig, $postId);
});

$router->get('/contact',function() use ($twig, $contactController){
    $contactController->showContactForm($twig);
});

$router->post('/contact/sendMessage',function() use ($twig, $contactController, $mail){
    $contactController->sendMessage($twig, $mail);
});

$router->run();
