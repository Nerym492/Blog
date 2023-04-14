<?php

namespace App\Entity;

use DateTime;

class User
{

    /**
     * @var integer
     */
    private int $userId;

    /**
     * @var string
     */
    private string $mail;

    /**
     * @var string
     */
    private string $pseudo;

    /**
     * @var string
     */
    private string $lastName;

    /**
     * @var string
     */
    private string $firstName;

    /**
     * @var string
     */
    private string $password;

    /**
     * @var DateTime
     */
    private DateTime $lastUpdateDate;

    /**
     * @var DateTime
     */
    private DateTime $creationDate;

    /**
     * @var integer
     */
    private int $userTypeId;

    /**
     * @var boolean
     */
    private bool $isAdmin;


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
    public function getMail(): string
    {
        return $this->mail;

    }//end getMail()


    /**
     * @param string $mail New value of mail
     * @return void
     */
    public function setMail(string $mail): void
    {
        $this->mail = $mail;

    }//end setMail()


    /**
     * @return string
     */
    public function getPseudo(): string
    {
        return $this->pseudo;

    }//end getPseudo()


    /**
     * @param string $pseudo New value of pseudo
     * @return void
     */
    public function setPseudo(string $pseudo): void
    {
        $this->pseudo = $pseudo;

    }//end setPseudo()


    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;

    }//end getLastName()


    /**
     * @param string $lastName New value of lastName
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;

    }//end setLastName()


    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;

    }//end getFirstName()


    /**
     * @param string $firstName New value of firstName
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;

    }//end setFirstName()


    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;

    }//end getPassword()


    /**
     * @param string $password New value of password
     * @return void
     */
    public function setPassword(string $password): void
    {
        $this->password = $password;

    }//end setPassword()


    /**
     * @return DateTime
     */
    public function getLastUpdateDate(): DateTime
    {
        return $this->lastUpdateDate;

    }//end getLastUpdateDate()


    /**
     * @param DateTime $lastUpdateDate New value of lastUpdateDate
     * @return void
     */
    public function setLastUpdateDate(DateTime $lastUpdateDate): void
    {
        $this->lastUpdateDate = $lastUpdateDate;

    }//end setLastUpdateDate()


    /**
     * @return DateTime
     */
    public function getCreationDate(): DateTime
    {
        return $this->creationDate;

    }//end getCreationDate()


    /**
     * @param DateTime $creationDate New value of creationDate
     * @return void
     */
    public function setCreationDate(DateTime $creationDate): void
    {
        $this->creationDate = $creationDate;

    }//end setCreationDate()


    /**
     * @return int
     */
    public function getUserTypeId(): int
    {
        return $this->userTypeId;

    }//end getUserTypeId()


    /**
     * @param int $userTypeId New value of userTypeId
     * @return void
     */
    public function setUserTypeId(int $userTypeId): void
    {
        $this->userTypeId = $userTypeId;
        $this->setIsAdmin();

    }//end setUserTypeId()


    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isAdmin;

    }//end isAdmin()


    /**
     * @return void
     */
    private function setIsAdmin(): void
    {
        if ($this->userTypeId < 3) {
            $this->isAdmin = true;
            return;
        }

        $this->isAdmin = false;

    }//end setIsAdmin()


}//end class
