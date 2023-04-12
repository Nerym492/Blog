<?php

namespace App\EntityManager;

use App\Entity\Comment;
use App\Lib\Session;

class CommentManager extends Manager
{


    public function getCommentsByPost(int $postId): ?array
    {

        $statement = $this->connection->getConnection()->prepare(
            "SELECT c.*, u.pseudo
             FROM comment c 
             LEFT OUTER JOIN user u ON c.user_id = u.user_id
             WHERE post_id = :post_id"
        );

        $statement->execute([':post_id' => $postId]);
        $rows = $statement->fetchAll();
        $comments = $this->createCommentsWithRows($rows);

        $statement->closeCursor();

        return $comments;

    }//end getCommentsByPost()


    public function getCommentsListWithLimit(int $pageNum, int $commentLimit, string $filter = ""): ?array
    {
        $selectQuery = "SELECT c.*, u.pseudo
                        FROM comment c 
                        LEFT OUTER JOIN user u ON c.user_id = u.user_id";

        $countQuery = "SELECT COUNT(*) as 'rowsCount'
                       FROM (" . $selectQuery . ") sq";

        $countQueryStatement = $this->connection->getConnection()->prepare($countQuery);
        $countQueryStatement->execute();
        $commentsRowsCount = $countQueryStatement->fetch()['rowsCount'];

        if ($commentsRowsCount > 0) {
            // Recalculate offset and limit parameters.
            $pageDelimitation = $this->calcPageAndOffset($commentLimit, $pageNum, $commentsRowsCount, "DESC");
            $rows = $this->connection->execQueryWithLimit(
                $pageDelimitation['rowsLimit'], $pageDelimitation['offset'], $selectQuery,
                "comment_id", "DESC"
            );
        } else {
            $pageDelimitation['pageNum'] = $pageNum;
            $rows = [];
        }

        // Creating a new comment object to store the data.
        $comments = $this->createCommentsWithRows($rows);

        return [
                'data'        => $comments,
                'nbLines'     => $commentsRowsCount,
                'currentPage' => $pageDelimitation['pageNum'],
               ];
    }//end getCommentsListWithLimit()


    /**
     * Create a new comment
     * @param int $postId Id of the Post that is currently been read
     * @return void
     */
    public function createComment(int $postId , Session $session): bool
    {
        $statement = $this->connection->getConnection()->prepare(
            "INSERT INTO blog.comment
                   (post_id, user_id, comment, creation_date, valid)
                   VALUES(:post_id, :user_id, :comment, :creation_date, :valid);"
        );

        $dateNow = new \DateTime('now', new \DateTimeZone($_ENV['TIMEZONE']));
        $dateNow = $dateNow->format('Y-m-d H:i:s');

        $statement->execute(
            [
             ':post_id'       => $postId,
             ':user_id'       => $_SESSION['user_id'],
             ':comment'       => strip_tags($_POST['comment']),
             ':creation_date' => $dateNow,
             ':valid'         => 0,
            ]
        );

        if ($statement->rowCount() === 1) {
            $commentCreated = true;
            $session->set('message', 'Your comment has been added !');
            $session->set('messageClass', 'success');
        } else {
            $commentCreated = false;
            $session->set('message', 'An error occurred while adding the comment.\nPlease try again later.');
            $session->set('messageClass', 'danger');
        }
        return $commentCreated;

    }//end createComment()


    public function deleteComment(int $commentId): bool
    {
        $statement = $this->connection->getConnection()->prepare(
            "DELETE FROM blog.comment 
                   WHERE comment_id=:commentId"
        );

        $statement->execute([':commentId' => $commentId]);

        return $statement->rowCount() == 1;
    }//end deleteComment()


    public function validateComment(int $commentId): bool
    {
        $statement = $this->connection->getConnection()->prepare(
            "UPDATE blog.comment
                   SET valid = 1 
                   WHERE comment_id=:commentId"
        );

        $statement->execute([':commentId' => $commentId]);

        return $statement->rowCount() == 1;
    }//end validateComment()


    private function createCommentsWithRows(array $rows): array
    {
        $comments = [];

        foreach ($rows as $row) {
            $comment = new Comment();
            $comment->setCommentId($row['comment_id']);
            $comment->setPostId($row['post_id']);
            $comment->setUserId($row['user_id']);
            $comment->setComment($row['comment']);
            $comment->setCreationDate($row['creation_date']);
            $comment->setValid($row['valid']);
            $comments[$comment->getcommentId()] = [
                                                   'line'       => $comment,
                                                   'userPseudo' => $row['pseudo'],
                                                  ];
        }

        return $comments;
    }//end createCommentsWithRows()

}//end class

