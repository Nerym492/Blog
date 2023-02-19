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
}