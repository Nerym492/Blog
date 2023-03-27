<?php

namespace App\EntityManager;

use App\Lib\DatabaseConnection;
use App\Entity\Comment;

class CommentManager
{
    public function getComments(int $postId): ?array
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
        $comments = null;

        foreach ($result as $row) {
            $comment = new Comment();
            $comment->setCommentId($row['comment_id']);
            $comment->setPostId($row['post_id']);
            $comment->setUserId($row['user_id']);
            $comment->setComment($row['comment']);
            $comment->setCreationDate(new \DateTime($row['creation_date']));
            $comment->setValid($row['valid']);
            $comments[$comment->getcommentId()] = ['line' => $comment, 'userPseudo' => $row['pseudo']];
        }

        $statement->closeCursor();

        return $comments;

    }

    /**
     * Create a new comment
     * @param int $postId Id of the Post that is currently been read
     * @return bool Return true if a comment has been created otherwise False
     */
    public function createComment(int $postId): bool
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "INSERT INTO blog.comment
                   (post_id, user_id, comment, creation_date, valid)
                   VALUES(:post_id, :user_id, :comment, :creation_date, :valid);"
        );


        $dateNow = new \DateTime('now', new \DateTimeZone($_ENV['TIMEZONE']));
        $dateNow = $dateNow->format('Y-m-d H:i:s');

        $statement->execute([
            ':post_id' => $postId,
            ':user_id' => $_SESSION['user_id'],
            ':comment' => strip_tags($_POST['comment']),
            ':creation_date' => $dateNow,
            ':valid' => 0
            ]);

        //True if a line has been created otherwise False
        return $statement->rowCount() == 1;

    }
}

//var_dump($_POST);