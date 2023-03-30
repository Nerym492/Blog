<?php

namespace App\Entity;

class Post
{
    private int $postId;
    private int $userId;
    private string $title;
    private string $excerpt;
    private string $content;
    private \DateTime $lastUpdateDate;
    private \DateTime $creationDate;

    public function getPostId()
    {
        return $this->postId;
    }

    public function setPostId(int $postId)
    {
        $this->postId = $postId;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function getExcerpt()
    {
        return $this->excerpt;
    }

    public function setExcerpt(string $excerpt)
    {
        $this->excerpt = $excerpt;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getLastUpdateDate()
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateDate(\DateTime $lastUpdateDate)
    {
        $this->lastUpdateDate = $lastUpdateDate;
    }


}