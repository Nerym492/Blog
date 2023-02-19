<?php

namespace App\Entity;

class User
{
    private int $userId;
    private string $mail;
    private string $pseudo;
    private string $lastName;
    private string $firstName;
    private \DateTime $lastUpdateDate;
    private \DateTime $creationDate;
}