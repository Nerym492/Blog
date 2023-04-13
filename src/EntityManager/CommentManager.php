<?php

namespace App\EntityManager;

use App\Entity\Comment;
use App\Lib\DatabaseConnection;
use App\Lib\Session;
use DateTime;

class CommentManager extends Manager
{


    /**
     * Gets all comments for a given post
     *
     * @param int $postId Id of the post
     * @return array|null
     */
    public function getCommentsByPost(int $postId): ?array
    {

        $statement = $this->database->prepare(
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


    /**
     * Gets a specific number of comments in ascending or descending order
     *
     * @param int $pageNum      Number of the page currently being read
     * @param int $commentLimit Limit of comments
     * @return array|null
     */
    public function getCommentsListWithLimit(int $pageNum, int $commentLimit): ?array
    {
        $selectQuery = "SELECT c.*, u.pseudo
                        FROM comment c 
                        LEFT OUTER JOIN user u ON c.user_id = u.user_id";

        $countQuery = "SELECT COUNT(*) as 'rowsCount'
                       FROM (" . $selectQuery . ") sq";

        $countQueryStatement = $this->database->prepare($countQuery);
        $countQueryStatement->execute();
        $commentsRowsCount = $countQueryStatement->fetch()['rowsCount'];

        if ($commentsRowsCount > 0) {
            // Recalculate offset and limit parameters.
            $pageDelimitation = $this->calcPageAndOffset($commentLimit, $pageNum, $commentsRowsCount, "DESC");
            $rows = DatabaseConnection::getInstance($this->session, $this->env)->execQueryWithLimit(
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
     *
     * @param int $postId Id of the Post that is currently been read
     * @return bool True is the comment has been created else false
     * @throws \Exception
     */
    public function createComment(int $postId): bool
    {
        $statement = $this->database->prepare(
            "INSERT INTO blog.comment
                   (post_id, user_id, comment, creation_date, valid)
                   VALUES(:post_id, :user_id, :comment, :creation_date, :valid);"
        );

        $dateNow = new DateTime('now', new \DateTimeZone($this->env->getVar('TIMEZONE')));
        $dateNow = $dateNow->format('Y-m-d H:i:s');

        $statement->execute(
            [
             ':post_id'       => $postId,
             ':user_id'       => $this->session->get('user_id'),
             ':comment'       => filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
             ':creation_date' => $dateNow,
             ':valid'         => 0,
            ]
        );

        if ($statement->rowCount() === 0) {
            $this->session->set('message', 'An error occurred while adding the comment.\nPlease try again later.');
            $this->session->set('messageClass', 'danger');
            return false;
        }

        $this->session->set('message', 'Your comment has been added !');
        $this->session->set('messageClass', 'success');

        return true;

    }//end createComment()


    /**
     * Delete a comment
     *
     * @param int $commentId Id of the comment to delete
     * @return bool
     */
    public function deleteComment(int $commentId): bool
    {
        $statement = $this->database->prepare(
            "DELETE FROM blog.comment 
                   WHERE comment_id=:commentId"
        );

        $statement->execute([':commentId' => $commentId]);

        return $statement->rowCount() == 1;

    }//end deleteComment()


    /**
     * Validate a comment
     *
     * @param int $commentId Id of the comment to validate
     * @return bool
     */
    public function validateComment(int $commentId): bool
    {
        $statement = $this->database->prepare(
            "UPDATE blog.comment
                   SET valid = 1 
                   WHERE comment_id=:commentId"
        );

        $statement->execute([':commentId' => $commentId]);

        return $statement->rowCount() == 1;

    }//end validateComment()


    /**
     * Create a comment object with rows from the database.
     *
     * @param array $rows Rows from the comment table
     * @return array
     */
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

