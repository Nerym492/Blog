<?php

namespace App\Controllers;

use \Twig\Environment as Twig;
use App\EntityManager\PostManager;
use App\EntityManager\UserManager;
use App\EntityManager\CommentManager;
use Pagination\Pagination;
use Pagination\StrategySimple;

class PostController
{
    public function showPosts(Twig $twig, int $pageNum): void
    {
        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum);

        $twig->addGlobal('session', $_SESSION);

        //use pagination class with results, per page and page
        $pagination = new Pagination(count($posts), 2, $pageNum);
        //get indexes in page
        $indexes = $pagination->getIndexes(new StrategySimple(2));
        $iterator = $indexes->getIterator();

        $paginationMenu = [
            'firstPage' => $pagination->getFirstPage(),
            'lastPage' => $pagination->getLastPage(),
            'previousPage' => $pagination->getPreviousPage(),
            'nextPage' => $pagination->getNextPage(),
            'activePage' => $pagination->getPage()
        ];

        echo $twig->render('posts.twig', [
            'posts' => $posts,
            'page' => 'Blog posts',
            'iterator' => $iterator,
            'paginationMenu' => $paginationMenu
        ]);

        if (isset($_SESSION['message'])) {
            unset($_SESSION['message']);
            unset($_SESSION['messageClass']);
        }
    }

    public function showPost(Twig $twig, int $postId): void
    {
        $postManager = new PostManager();
        $post = $postManager->getPost($postId);
        $userManager = new UserManager();
        $userPost = $userManager->getUser(userId: $post->getUserId());
        $commentManager = new CommentManager();
        $comments = $commentManager->getComments($postId);

        echo $twig->render('post.twig', [
            'post' => $post,
            'userPost' => $userPost,
            'comments' => $comments
        ]);

        if (isset($_SESSION['message'])) {
            unset($_SESSION['message']);
            unset($_SESSION['messageClass']);
        }
    }
}