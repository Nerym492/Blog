<?php

namespace App\Controllers;

use Exception;


class AdminController extends AbstractController
{
    private const POST_LIMIT    = 3;
    private const COMMENT_LIMIT = 4;


    /**
     * Displays the Admin panel with the posts and comments list
     *
     * @return void
     * @throws Exception Error from database.
     */
    public function showAdminPanel(): void
    {
        $pageNum             = 1;
        $posts               = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
        $postsPaginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        $commentsContainer = $this->getCommentsContainer($pageNum);
        $commentCssClass       = $this->getCommentCssClass();

        $this->renderView(
            'adminPanel.twig',
            [
             'page'                   => 'Administration',
             'posts'                  => $posts['data'],
             'postsPaginationMenu'    => $postsPaginationMenu,
             'comments'               => $commentsContainer['comments']['data'],
             'commentsPaginationMenu' => $commentsContainer['paginationMenu'],
             'commentCssClass'        => $commentCssClass,
            ]
        );

    }//end showAdminPanel()


    /**
     * Only reloads the post container in the page that is currently in use
     *
     * @param integer $pageNum Number of the page in the post list pagination.
     *
     * @return void
     * @throws Exception An error from the database.
     */
    public function reloadPostsList(int $pageNum): void
    {
        $posts          = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        $this->renderView(
            'partials/postsList.twig',
            [
             'posts'          => $posts['data'],
             'paginationMenu' => $paginationMenu,
            ]
        );

    }//end reloadPostsList()


    /**
     * Only reloads the comment container in the page that is currently in use
     *
     * @param integer $pageNum Number of the page in the post list pagination.
     *
     * @return void
     * @throws Exception
     */
    public function reloadCommentsList(int $pageNum): void
    {
        $commentsContainer = $this->getCommentsContainer($pageNum);

        $commentCssClass = $this->getCommentCssClass();

        $this->renderView(
            'partials/commentsList.twig',
            [
             'comments'        => $commentsContainer['comments']['data'],
             'paginationMenu'  => $commentsContainer['paginationMenu'],
             'commentCssClass' => $commentCssClass,
            ]
        );

    }//end reloadCommentsList()


    /**
     * Delete a post from the list / database.
     * All comments linked to this post are automatically deleted
     *
     * @param integer $pageNum Page being read in the posts list pagination.
     * @param integer $postId  Post that the user wants to delete.
     *
     * @return void
     * @throws Exception Database error.
     */
    public function deletePost(int $pageNum, int $postId): void
    {
        $this->postManager->deletePost($postId);
        $posts          = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
        $paginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        $this->renderView(
            'partials/postsList.twig',
            [
             'posts'          => $posts['data'],
             'paginationMenu' => $paginationMenu,
            ]
        );

    }//end deletePost()


    /**
     * Validate a comment
     *
     * @param integer $pageNum       Page being read in the comments list pagination.
     * @param integer $commentId     Comment that the user wants to validate.
     * @param bool    $cancelComment Reset the comment status when true.
     * @return void
     * @throws Exception
     */
    public function validateComment(int $pageNum, int $commentId, bool $cancelComment=false): void
    {
        $this->commentManager->setCommentValidity($commentId, $cancelComment);

        $commentsContainer = $this->getCommentsContainer($pageNum);
        $commentCssClass       = $this->getCommentCssClass();

        $this->renderView(
            'partials/commentsList.twig',
            [
             'comments'        => $commentsContainer['comments']['data'],
             'paginationMenu'  => $commentsContainer['paginationMenu'],
             'commentCssClass' => $commentCssClass,
            ]
        );

    }//end validateComment()


    /**
     * Delete a comment
     *
     * @param integer $pageNum   Page being read in the comments list pagination.
     * @param integer $commentId Comment that the user wants to delete.
     *
     * @return void
     * @throws Exception
     */
    public function deleteComment(int $pageNum, int $commentId): void
    {
        $this->commentManager->deleteComment($commentId);

        $commentsContainer = $this->getCommentsContainer($pageNum);
        $commentCssClass       = $this->getCommentCssClass();

        $this->renderView(
            'partials/commentsList.twig',
            [
             'comments'        => $commentsContainer['comments']['data'],
             'paginationMenu'  => $commentsContainer['paginationMenu'],
             'commentCssClass' => $commentCssClass,
            ]
        );

    }//end deleteComment()


    /**
     * Retrieve all the necessary data to reload the comment container
     *
     * @param integer $pageNum Page being read in the comments list pagination.
     *
     * @return array Access to comments with 'comments' key and pagination with 'paginationMenu' key
     * @throws Exception
     */
    private function getCommentsContainer(int $pageNum): array
    {
        $comments       = $this->commentManager->getCommentsListWithLimit($pageNum, self::COMMENT_LIMIT);
        $paginationMenu = $this->getPagination($comments['nbLines'], self::COMMENT_LIMIT, $pageNum);

        foreach ($comments['data'] as $commentKey => $commentArray) {
            $comments['data'][$commentKey]['post'] = $this->postManager->getPost($commentArray['line']->getPostId());

            $commentIsValid = $comments['data'][$commentKey]['line']->getValid();
            if ($commentIsValid === 0) {
                $comments['data'][$commentKey]['badgeClass'] = 'warning';
                $comments['data'][$commentKey]['badgeText']  = 'Pending';
            } else if ($commentIsValid === 1) {
                $comments['data'][$commentKey]['badgeClass'] = 'success';
                $comments['data'][$commentKey]['badgeText']  = 'Validated';
            }
        }

        return [
                'comments'       => $comments,
                'paginationMenu' => $paginationMenu,
               ];

    }//end getCommentsContainer()


    /**
     * Store comment container css classes in an array
     *
     * @return array
     */
    private function getCommentCssClass(): array
    {
        return [
                'commentLine'       => 'comment-line-admin',
                'flexDirection'     => 'flex-column badge-active',
                'commentListMargin' => 'mx-auto',
               ];

    }//end getCommentCssClass()


}//end class
