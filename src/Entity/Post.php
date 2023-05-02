<?php

namespace App\Entity;

use DateTime;
use Exception;

class Post
{

    /**
     * @var integer
     */
    private int $postId;

    /**
     * @var integer
     */
    private int $userId;

    /**
     * @var string
     */
    private string $title;

    /**
     * @var string
     */
    private string $excerpt;

    /**
     * @var string
     */
    private string $content;

    /**
     * @var DateTime
     */
    private DateTime $lastUpdateDate;

    /**
     * @var DateTime
     */
    private DateTime $creationDate;


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
    public function getTitle(): string
    {
        return $this->title;

    }//end getTitle()


    /**
     * @param string $title New value of title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;

    }//end setTitle()


    /**
     * @return string
     */
    public function getExcerpt(): string
    {
        return $this->excerpt;

    }//end getExcerpt()


    /**
     * @param string $excerpt New value of excerpt
     * @return void
     */
    public function setExcerpt(string $excerpt): void
    {
        $this->excerpt = $excerpt;

    }//end setExcerpt()


    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;

    }//end getContent()


    /**
     * @param string $content New value of content
     * @return void
     */
    public function setContent(string $content): void
    {
        $this->content = $content;

    }//end setContent()


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
     * @return DateTime
     */
    public function getLastUpdateDate(): DateTime
    {
        return $this->lastUpdateDate;

    }//end getLastUpdateDate()


    /**
     * @param string $lastUpdateDate New value of lastUpdateDate
     * @return void
     * @throws Exception
     */
    public function setLastUpdateDate(string $lastUpdateDate): void
    {
        $this->lastUpdateDate = new DateTime($lastUpdateDate);

    }//end setLastUpdateDate()


    /**
     * @return array Return an associative array of the object
     */
    public function getProperties(): array
    {
        return get_object_vars($this);

    }//end getProperties()


}//end class
