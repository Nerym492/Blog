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

    /**
     * @param int $rowLimit Number of lines max returned by the query
     * @param int $offset OFFSET in the LIMIT clause
     * @param string $selectQuery Select sub request to execute with the limit
     * @param string $orderBy Sorted database field
     * @param string $orderBySuffix ASC or DESC
     * @return array
     */
    public function execQueryWithLimit(int $rowLimit, int $offset, string $selectQuery, string $orderBy, string $orderBySuffix = ""): array
    {
        $orderByString = "ORDER BY " . $orderBy;

        if (in_array($orderBySuffix, ['ASC', 'DESC'])){
            $orderByString .= " ". $orderBySuffix;
        }

        $connexion = $this->getConnection();
        $connexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

        $statement = $connexion->prepare(
            "SELECT *
                       FROM (
                            " . $selectQuery . "
                            LIMIT :limitParam OFFSET :offsetParam
                        ) p
                    " . $orderByString
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