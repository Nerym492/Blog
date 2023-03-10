<?php

namespace App\Controllers;

use \Twig\Environment as Twig;
use App\EntityManager\PostManager;
use App\EntityManager\UserManager;


class PostController
{
    public function showPosts(Twig $twig): void
    {
        $postManager = new PostManager();
        $posts = $postManager->getPosts();

        echo $twig->render('posts.twig', ['posts' => $posts, 'page' => 'Blog posts']);
    }

    public function showPost(Twig $twig, int $postId): void
    {
        $postManager = new PostManager();
        $post = $postManager->getPost($postId);
        $userManager = new UserManager();
        $userPost = $userManager->getUser($post->getUserId());

        echo $twig->render('post.twig', ['post' => $post, 'userPost' => $userPost]);
    }
}