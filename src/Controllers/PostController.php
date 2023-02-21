<?php

namespace App\Controllers;

use \Twig\Environment as Twig;
use App\EntityManager\PostManager;


class PostController
{
    public function afficherPosts(Twig $twig): void
    {
        $postManager = new PostManager();
        $posts = $postManager->getPosts();

        echo $twig->render('posts.twig', ['posts' => $posts]);
    }
}