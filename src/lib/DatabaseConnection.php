<?php

namespace App\Lib;

use \PDO;


class DatabaseConnection
{
    private ?PDO $database = null;

    public function getConnection(): ?PDO
    {
        if ($this->database === null) {
            try {
                $this->database = new PDO('mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] .
                    ';charset=utf8', $_ENV['DB_USER'], $_ENV['DB_PASS']);
            } catch (\Exception $e) {
                $_SESSION['message'] = "An error occurred while connecting to the database.\nPlease try again later.";
                $_SESSION['messageClass'] = "danger";
            }
        }

        return $this->database;
    }
}