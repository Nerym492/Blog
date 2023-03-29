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
    public function showPostsPage(Twig $twig): void
    {
        $postsListData = $this->getPostsListData($twig, postLimit: 3);

        echo $twig->render('posts.twig', [
            'posts' => $postsListData['posts'],
            'page' => 'Blog posts',
            'paginationMenu' => $postsListData['paginationMenu']
        ]);

        if (isset($_SESSION['message'])) {
            unset($_SESSION['message']);
            unset($_SESSION['messageClass']);
        }
    }

    public function showPostsWidget(Twig $twig, int $pageNum): void
    {
        $postsListData = $this->getPostsListData($twig, $pageNum, 3);

        echo $twig->render('partials/postsList.twig', [
            'posts' => $postsListData['posts'],
            'paginationMenu' => $postsListData['paginationMenu'],
        ]);
    }

    private function getPostsListData(Twig $twig, int $pageNum = 1, int $postLimit = 4): array
    {
        //Number of posts per page
        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum, $postLimit);

        $twig->addGlobal('session', $_SESSION);

        //use pagination class with results, per page and page
        $pagination = new Pagination($posts['nbLines'], $postLimit, $pageNum);
        //get indexes in page
        $indexes = $pagination->getIndexes(new StrategySimple(5));

        $paginationMenu = [
            'firstPage' => $pagination->getFirstPage(),
            'lastPage' => $pagination->getLastPage(),
            'previousPage' => $pagination->getPreviousPage(),
            'nextPage' => $pagination->getNextPage(),
            'activePage' => $pagination->getPage(),
            'iterator' => $indexes->getIterator()
        ];

        return ['paginationMenu' => $paginationMenu, 'posts' => $posts['data']];
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