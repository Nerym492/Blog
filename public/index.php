<?php

require '../vendor/autoload.php';

use \App\Controllers\PostController;

$loader = new \Twig\Loader\FilesystemLoader('../Templates');
$twig = new \Twig\Environment($loader, [
    'debug' => true,
    'cache' => '../tmp',
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());
//var_dump(get_included_files());

$router = new \Bramus\Router\Router();

$postController = new PostController();

$router->get('/homepage',function() use ($twig, $postController){
    $postController = new PostController();
    $postController->afficherPosts($twig);
});


$router->run();
