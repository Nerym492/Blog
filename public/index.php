<?php

require '../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../Templates');
$twig = new \Twig\Environment($loader, [
    'cache' => '../tmp',
]);

echo $twig->render('base.twig');