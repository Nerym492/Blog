<?php

namespace App\Controllers;

use \Twig\Environment as Twig;
use App\EntityManager\PostManager;
use App\EntityManager\UserManager;
use App\EntityManager\CommentManager;

class PostController extends AbstractController
{
    const POST_LIMIT = 3;
    const NB_MAX_PAGE = 5;
    public function showPostsPage(Twig $twig): void
    {
        $postsListData = $this->getPostsListData($twig);

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

    public function reloadPostsList(Twig $twig, int $pageNum): void
    {
        $postsListData = $this->getPostsListData($twig, $pageNum);

        echo $twig->render('partials/postsList.twig', [
            'posts' => $postsListData['posts'],
            'paginationMenu' => $postsListData['paginationMenu'],
        ]);
    }

    private function getPostsListData(Twig $twig, int $pageNum = 1): array
    {
        //Number of posts per page
        $postManager = new PostManager();
        $posts = $postManager->getPosts($pageNum, self::POST_LIMIT);

        $twig->addGlobal('session', $_SESSION);

        //use pagination class with results, per page and page
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        return ['paginationMenu' => $paginationMenu, 'posts' => $posts['data']];
    }

    public function showPost(Twig $twig, int $postId): void
    {
        $postManager = new PostManager();
        $post = $postManager->getPost($postId);
        $userManager = new UserManager();
        $userPost = $userManager->getUser(userId: $post->getUserId());
        $commentManager = new CommentManager();
        $comments = $commentManager->getCommentsByPost($postId);

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