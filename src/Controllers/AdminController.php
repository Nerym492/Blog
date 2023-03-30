<?php

namespace App\Controllers;

use \Twig\Environment as Twig;
use App\EntityManager\PostManager;

class AdminController extends AbstractController
{
    public function showAdminPanel(Twig $twig): void
    {
        $pageNum = 1;
        $postLimit = 4;
        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum, $postLimit);

        $paginationMenu = $this->getPagination($posts['nbLines'], $postLimit, $pageNum);

        echo $twig->render('adminPanel.twig', [
            'page' => 'Administration',
            'posts' => $posts['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }

    public function reloadPostsList(Twig $twig, int $pageNum)
    {
        $postLimit = 4;

        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum, $postLimit);
        $paginationMenu = $this->getPagination($posts['nbLines'], $postLimit, $pageNum);

        echo $twig->render('partials/postsList.twig', [
            'posts' => $posts['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }


}