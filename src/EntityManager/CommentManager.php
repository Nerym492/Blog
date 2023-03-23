<?php

namespace App\EntityManager;

use App\Lib\DatabaseConnection;
use App\Entity\Comment;

class CommentManager
{
    public function getComments(int $postId): array
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "SELECT c.*, u.pseudo
             FROM comment c
             LEFT OUTER JOIN user u ON c.user_id = u.user_id 
             WHERE post_id = :post_id"
        );

        $statement->execute([':post_id' => $postId]);
        $result = $statement->fetchAll();
        $comments = [];

        foreach ($result as $row) {
            $comment = new Comment();
            $comment->setCommentId($row['comment_id']);
            $comment->setPostId($row['post_id']);
            $comment->setUserId($row['user_id']);
            $comment->setComment($row['comment']);
            $comment->setCreationDate(new \DateTime($row['creation_date']));
            $comment->setValid($row['valid']);
            $comments[$comment->getcommentId()] = [
                'comment' => $comment,
                'userPseudo' => $row['pseudo']
            ];
        }

        $statement->closeCursor();

        return $comments;

    }
}