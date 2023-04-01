<?php

namespace App\Controllers;

use \Twig\Environment as Twig;
use App\EntityManager\PostManager;

class AdminController extends AbstractController
{
    const POST_LIMIT = 4;
    const NB_MAX_PAGE = 5;

    public function showAdminPanel(Twig $twig): void
    {
        $pageNum = 1;
        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum, self::POST_LIMIT);

        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        echo $twig->render('adminPanel.twig', [
            'page' => 'Administration',
            'posts' => $posts['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }

    public function reloadPostsList(Twig $twig, int $pageNum)
    {
        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum, self::POST_LIMIT);
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        echo $twig->render('partials/postsList.twig', [
            'posts' => $posts['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }

    public function deletePost(Twig $twig, int $pageNum, int $postId)
    {
        $postManager = new PostManager();
        $postManager->deletePost($postId);
        $posts = $postManager->getPosts($pageNum, self::POST_LIMIT);
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        echo $twig->render('partials/postsList.twig', [
            'posts' => $posts['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }


}