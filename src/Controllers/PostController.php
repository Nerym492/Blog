<?php

namespace App\Controllers;

use Twig\Environment as Twig;
use App\EntityManager\PostManager;
use App\EntityManager\UserManager;
use App\EntityManager\CommentManager;

/**
 * Post Controller, used only when the url contains 'posts/'
 */
class PostController extends AbstractController
{
    private const POST_LIMIT = 3;


    /**
     * Display the posts page.
     *
     * @return void
     * @throws \Exception Database error.
     */
    public function showPostsPage(): void
    {
        $postsListData = $this->getPostsListData();

        $this->renderView(
            'posts.twig',
            [
             'posts'          => $postsListData['posts'],
             'page'           => 'Blog posts',
             'paginationMenu' => $postsListData['paginationMenu'],
            ]
        );

        $this->session->clearKeys(['message', 'messageClass']);

    }//end showPostsPage()


    /**
     * Only reload the posts container
     *
     * @param integer $pageNum Number of the page in the post list pagination.
     *
     * @return void
     * @throws \Exception Database error.
     */
    public function reloadPostsList(int $pageNum): void
    {
        $postsListData = $this->getPostsListData($pageNum);

        $this->renderView(
            'partials/postsList.twig',
            [
             'posts'          => $postsListData['posts'],
             'paginationMenu' => $postsListData['paginationMenu'],
            ]
        );

    }//end reloadPostsList()


    /**
     * Returns a limited number of posts in a specific order
     *
     * @param integer $pageNum Number of the page in the post list pagination.
     *
     * @return array
     * @throws \Exception Database error.
     */
    private function getPostsListData(int $pageNum=1): array
    {
        // Number of posts per page.
        $postManager = new PostManager();
        $posts       = $postManager->getPosts($pageNum, self::POST_LIMIT);

        $this->setTwigSessionGlobals();

        // Use pagination class with results, per page and page.
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        return [
                'paginationMenu' => $paginationMenu,
                'posts'          => $posts['data'],
               ];

    }//end getPostsListData()


    /**
     * Display a post
     *
     * @param integer $postId Id of the post being read.
     *
     * @return void
     */
    public function showPost(int $postId): void
    {
        $postManager    = new PostManager();
        $post           = $postManager->getPost($postId);
        $userManager    = new UserManager();
        $userPost       = $userManager->getUser(userId: $post->getUserId());
        $commentManager = new CommentManager();
        $comments       = $commentManager->getCommentsByPost($postId);

        $this->renderView(
            'post.twig',
            [
             'post'     => $post,
             'userPost' => $userPost,
             'comments' => $comments,
            ]
        );

        $this->session->clearKeys(['message', 'messageClass']);

    }//end showPost()


}//end class

