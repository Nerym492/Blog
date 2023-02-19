<?php

namespace App\Entity;

class Comment
{
    private int $postId;
    private int $userId;
    private string $title;
    private string $excerpt;
    private string $content;
    private \DateTime $lastUpdateDate;
    private \DateTime $creationDate;

    
}