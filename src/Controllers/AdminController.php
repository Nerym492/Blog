<?php

namespace App\Controllers;

use App\EntityManager\CommentManager;
use \Twig\Environment as Twig;
use App\EntityManager\PostManager;

class AdminController extends AbstractController
{
    const POST_LIMIT = 3;
    const COMMENT_LIMIT = 4;
    const NB_MAX_PAGE = 5;

    public function showAdminPanel(Twig $twig): void
    {
        $pageNum = 1;
        $posts = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
        $postsPaginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        $comments = $this->commentManager->getCommentsListWithLimit($pageNum, self::COMMENT_LIMIT);
        $commentsPaginationMenu = $this->getPagination($comments['nbLines'], self::COMMENT_LIMIT, $pageNum);

        foreach ($comments['data'] as $commentKey => $commentArray)
        {
            $comments['data'][$commentKey]['post'] = $this->postManager->getPost($commentArray['line']->getPostId());
        }

        echo $twig->render('adminPanel.twig', [
            'page' => 'Administration',
            'posts' => $posts['data'],
            'postsPaginationMenu' => $postsPaginationMenu,
            'comments' => $comments['data'],
            'commentsPaginationMenu' => $commentsPaginationMenu
        ]);
    }

    public function reloadPostsList(Twig $twig, int $pageNum)
    {
        $posts = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);


        echo $twig->render('partials/postsList.twig', [
            'posts' => $posts['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }

    public function reloadCommentsList(Twig $twig, int $pageNum){
        $comments = $this->commentManager->getCommentsListWithLimit($pageNum, self::COMMENT_LIMIT);
        $paginationMenu = $this->getPagination($comments['nbLines'], self::COMMENT_LIMIT, $pageNum);

        foreach ($comments['data'] as $commentKey => $commentArray)
        {
            $comments['data'][$commentKey]['post'] = $this->postManager->getPost($commentArray['line']->getPostId());
        }

        echo $twig->render('partials/commentsList.twig', [
            'comments' => $comments['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }

    public function deletePost(Twig $twig, int $pageNum, int $postId)
    {
        $this->postManager->deletePost($postId);
        $posts = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        echo $twig->render('partials/postsList.twig', [
            'posts' => $posts['data'],
            'paginationMenu' => $paginationMenu
        ]);
    }


}