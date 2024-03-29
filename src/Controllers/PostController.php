<?php

namespace App\Controllers;

use Exception;
use Throwable;

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
     * @throws Exception Database error.
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
     * @throws Exception Database error.
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
     * @throws Exception Database error.
     */
    private function getPostsListData(int $pageNum=1): array
    {
        // Number of posts per page.
        $posts       = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
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
        try {
            $post           = $this->postManager->getPost($postId);
            $userPost       = $this->userManager->getUser(userId: $post->getUserId());
            $comments       = $this->commentManager->getCommentsByPost($postId);

            $this->renderView(
                'post.twig',
                [
                 'post'     => $post,
                 'userPost' => $userPost,
                 'comments' => $comments,
                ]
            );
        } catch (Throwable) {
            $this->redirectTo($this->env->getVar('PUBLIC_PATH')."/posts/");
        }

        $this->session->clearKeys(['message', 'messageClass']);

    }//end showPost()


}//end class
