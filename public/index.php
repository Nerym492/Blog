<?php

require '../vendor/autoload.php';

use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\ContactController;

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

$router->run();
