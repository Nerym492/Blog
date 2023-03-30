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

    public function execQueryWithLimit(int $nbRows, int $rowLimit, int $offset): array
    {

        $connexion = $this->getConnection();
        $connexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

        $statement = $connexion->prepare(
            "SELECT *
                       FROM (
                            SELECT p.post_id, p.user_id, p.title, p.excerpt, p.content, p.last_update_date, 
                                   p.creation_date, u.pseudo
                            from blog.post p
                            LEFT OUTER JOIN blog.user u on p.user_id = u.user_id
                            LIMIT :limitParam OFFSET :offsetParam
                        ) p
                    ORDER BY p.post_id DESC"
        );
        $statement->execute([
            ':limitParam' => $rowLimit,
            ':offsetParam' => $offset
        ]);

        $result = $statement->fetchAll();
        $statement->closeCursor();

        return $result;
    }

}