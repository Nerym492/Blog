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
    public function showPosts(Twig $twig, int $pageNum = 0): void
    {
        //The user has refreshed the page
        if ($pageNum == 0){
            $pageNum = 2;
            $pageRefreshed = true;
        } else {
            //pageNum has been sent with AJAX Get request
            $pageRefreshed = false;
        }

        //Number of posts per page
        $postLimit = 2;
        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum, $postLimit);

        $twig->addGlobal('session', $_SESSION);

        //use pagination class with results, per page and page
        $pagination = new Pagination($posts['nbLines'], $postLimit, $pageNum);
        //get indexes in page
        $indexes = $pagination->getIndexes(new StrategySimple(5));
        $iterator = $indexes->getIterator();

        $paginationMenu = [
            'firstPage' => $pagination->getFirstPage(),
            'lastPage' => $pagination->getLastPage(),
            'previousPage' => $pagination->getPreviousPage(),
            'nextPage' => $pagination->getNextPage(),
            'activePage' => $pagination->getPage()
        ];

        $pageParameters = [
            'posts' => $posts['data'],
            'page' => 'Blog posts',
            'iterator' => $iterator,
            'paginationMenu' => $paginationMenu,
            'pageRefreshed' => $pageRefreshed
        ];

        if ($pageRefreshed){
            echo $twig->render('posts.twig', $pageParameters);
        } else {
            unset($pageParameters['page']);
            echo $twig->render('partials/postsList.twig', $pageParameters);
        }

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