<?php

namespace App\Controllers;
use \Twig\Environment as Twig;

class AdminController extends AbstractController
{
    const POST_LIMIT = 3;
    const COMMENT_LIMIT = 4;

    public function showAdminPanel(Twig $twig): void
    {
        $pageNum = 1;
        $posts = $this->postManager->getPosts($pageNum, self::POST_LIMIT);
        $postsPaginationMenu = $this->getPagination($posts['nbLines'], self::POST_LIMIT, $pageNum);

        $commentsContainerData = $this->getCommentsContainerData($pageNum);

        $commentCssClass = $this->getCommentCssClass();

        echo $twig->render('adminPanel.twig', [
            'page' => 'Administration',
            'posts' => $posts['data'],
            'postsPaginationMenu' => $postsPaginationMenu,
            'comments' => $commentsContainerData['comments']['data'],
            'commentsPaginationMenu' => $commentsContainerData['paginationMenu'],
            'commentCssClass' => $commentCssClass
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
        $commentsContainerData = $this->getCommentsContainerData($pageNum);

        $commentCssClass = $this->getCommentCssClass();

        echo $twig->render('partials/commentsList.twig', [
            'comments' => $commentsContainerData['comments']['data'],
            'paginationMenu' => $commentsContainerData['paginationMenu'],
            'commentCssClass' => $commentCssClass
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

    public function deleteComment(Twig $twig, int $pageNum, int $commentId)
    {
        $this->commentManager->deleteComment($commentId);

        $commentsContainerData = $this->getCommentsContainerData($pageNum);
        $commentCssClass = $this->getCommentCssClass();

        echo $twig->render('partials/commentsList.twig', [
            'comments' => $commentsContainerData['comments']['data'],
            'paginationMenu' => $commentsContainerData['paginationMenu'],
            'commentCssClass' => $commentCssClass
        ]);

    }

    private function getCommentsContainerData(int $pageNum): array
    {
        $comments = $this->commentManager->getCommentsListWithLimit($pageNum, self::COMMENT_LIMIT);
        $paginationMenu = $this->getPagination($comments['nbLines'], self::COMMENT_LIMIT, $pageNum);

        foreach ($comments['data'] as $commentKey => $commentArray)
        {
            $comments['data'][$commentKey]['post'] = $this->postManager->getPost($commentArray['line']->getPostId());
            if ($comments['data'][$commentKey]['line']->getValid() === 0){
                $comments['data'][$commentKey]['badgeClass'] = "warning";
                $comments['data'][$commentKey]['badgeText'] = "En attente";
            } else {
                $comments['data'][$commentKey]['badgeClass'] = "success";
                $comments['data'][$commentKey]['badgeText'] = "Validé";
            }
        }

        return ['comments' => $comments, 'paginationMenu' => $paginationMenu];
    }

    private function getCommentCssClass(): array
    {
        $commentCssClass['commentLine'] = "comment-line-admin";
        $commentCssClass['flexDirection'] = "flex-column badge-active";
        $commentCssClass['commentListMargin'] = "mx-auto";

        return $commentCssClass;
    }
}