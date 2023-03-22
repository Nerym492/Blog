<?php

namespace App\Entity;

class User
{
    private int $userId;
    private string $mail;
    private string $pseudo;
    private string $lastName;
    private string $firstName;
    private string $password;
    private \DateTime $lastUpdateDate;
    private \DateTime $creationDate;
    private int $userTypeId;
    private bool $isAdmin;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getMail(): string
    {
        return $this->mail;
    }

    public function setMail(string $mail): void
    {
        $this->mail = $mail;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getLastUpdateDate(): \DateTime
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateDate(\DateTime $lastUpdateDate): void
    {
        $this->lastUpdateDate = $lastUpdateDate;
    }

    public function getCreationDate(): \DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getUserTypeId(): int
    {
        return $this->userTypeId;
    }

    public function setUserTypeId(int $userTypeId): void
    {
        $this->userTypeId = $userTypeId;
        $this->setIsAdmin();
    }

    public function getIsAdmin(): bool
    {
        return $this->isAdmin;
    }

    private function setIsAdmin(): bool
    {
        if ($this->userTypeId < 3) {
            $this->isAdmin = true;
        } else {
            $this->isAdmin = false;
        }

        return $this->isAdmin;
    }
}