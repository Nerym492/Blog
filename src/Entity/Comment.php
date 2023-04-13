<?php

namespace App\Entity;

use DateTime;
use Exception;

class Comment
{
    /**
     * @var integer $commentId
     */
    private int $commentId;
    /**
     * @var integer $postId
     */
    private int $postId;
    /**
     * @var integer $userId
     */
    private int $userId;
    /**
     * @var string $comment
     */
    private string $comment;
    /**
     * @var DateTime $creationDate
     */
    private DateTime $creationDate;
    /**
     * @var integer $valid
     */
    private int $valid;


    /**
     * @return int
     */
    public function getCommentId(): int
    {
        return $this->commentId;

    }//end getCommentId()


    /**
     * @param int $commentId New value of commentId
     * @return void
     */
    public function setCommentId(int $commentId): void
    {
        $this->commentId = $commentId;

    }//end setCommentId()


    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;

    }//end getPostId()


    /**
     * @param int $postId New value of postId
     * @return void
     */
    public function setPostId(int $postId): void
    {
        $this->postId = $postId;

    }//end setPostId()


    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;

    }//end getUserId()


    /**
     * @param int $userId New value of userId
     * @return void
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;

    }//end setUserId()


    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;

    }//end getComment()


    /**
     * @param string $comment New value of comment
     * @return void
     */
    public function setComment(string $comment): void
    {
        $this->comment = $comment;

    }//end setComment()


    /**
     * @return DateTime
     */
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;

    }//end getCreationDate()


    /**
     * @param string $creationDate New value of creationDate
     * @return void
     * @throws Exception
     */
    public function setCreationDate(string $creationDate): void
    {
        $this->creationDate = new DateTime($creationDate);

    }//end setCreationDate()


    /**
     * @return int
     */
    public function getValid(): int
    {
        return $this->valid;

    }//end getValid()


    /**
     * @param int $valid New value of valid
     * @return void
     */
    public function setValid(int $valid): void
    {
        $this->valid = $valid;

    }//end setValid()

}//end class

