<?php

require '../vendor/autoload.php';

use \App\Controllers\PostController;

$loader = new \Twig\Loader\FilesystemLoader('../Templates');
$twig = new \Twig\Environment($loader, [
    'cache' => '../tmp',
]);

//var_dump(get_included_files());

$router = new \Bramus\Router\Router();

$postController = new PostController();

$router->get('/',function() use ($twig, $postController){
    $postController = new PostController();
    $postController->afficherPosts($twig);
});

$router->run();

