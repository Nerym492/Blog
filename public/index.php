<?php

require '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../Templates');
$twig = new \Twig\Environment($loader, [
    'cache' => '../tmp',
]);

$router = new \Bramus\Router\Router();

$router->get('/',function() use ($twig){
    echo $twig->render('base.twig');
});

$router->run();

