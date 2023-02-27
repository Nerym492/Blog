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

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail(string $mail)
    {
        $this->mail = $mail;
    }

    public function getPseudo()
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo)
    {
        $this->pseudo = $pseudo;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword(string $password)
    {
        $this->password = $password;
    }

    public function getLastUpdateDate()
    {
        return $this->lastUpdateDate;
    }

    public function setLastUpdateDate(\DateTime $lastUpdateDate)
    {
        $this->lastUpdateDate = $lastUpdateDate;
    }

    public function getCreationDate()
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function getUserTypeId()
    {
        return $this->userTypeId;
    }

    public function setUserTypeId(int $userTypeId)
    {
        $this->userTypeId = $userTypeId;
        $this->setIsAdmin();
    }

    public function getIsAdmin(){
        return $this->isAdmin;
    }

    private function setIsAdmin()
    {
        if ($this->userTypeId < 3) {
            $isAdmin = true;
        } else {
            $isAdmin = false;
        }

        return $isAdmin;
    }
}