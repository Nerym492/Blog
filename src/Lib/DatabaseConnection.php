<?php

namespace App\Lib;

use \PDO;


class DatabaseConnection
{

    /**
     * PDO object or null if the connection fails
     *
     * @var PDO|null
     */
    private ?PDO $database = null;

    /**
     * @var ?DatabaseConnection Database instance
     */
    private static ?DatabaseConnection $instance = null;


    /**
     * Create a new database connection
     * @param Session     $session Session variables
     * @param Environment $env     Environment variables
     */
    public function __construct(Session $session, Environment $env)
    {
        try {
            $this->database = new PDO(
                'mysql:host='.$env->getVar('DB_HOST').';dbname='.$env->getVar('DB_NAME').';charset=utf8',
                $env->getVar('DB_USER'), $env->getVar('DB_PASS')
            );
        } catch (\Exception $e) {
            $session->set('message', 'An error occurred while connecting to the database.\nPlease try again later.');
            $session->set('messageClass', 'danger');
        }

    }//end __construct()


    /**
     * Create an DatabaseConnection instance if it does not exist
     *
     * @param Session     $session Session variables
     * @param Environment $env     Environment variables
     * @return DatabaseConnection
     */
    public static function getInstance(Session $session, Environment $env): DatabaseConnection
    {
        if (self::$instance === null) {
            self::$instance = new DatabaseConnection($session, $env);
        }

        return self::$instance;

    }//end getInstance()


    /**
     * Create a connection to the database
     *
     * @return PDO|null
     */
    public function getConnection(): ?PDO
    {
        return $this->database;

    }//end getConnection()


    /**
     * Execute a query with a limited number of rows and an ascending or descending sort
     *
     * @param int    $rowLimit      Number of lines max returned by the query
     * @param int    $offset        OFFSET in the LIMIT clause
     * @param string $selectQuery   Select sub request to execute with the limit
     * @param string $orderBy       Sorted database field
     * @param string $orderBySuffix ASC or DESC
     * @return array
     */
    public function execQueryWithLimit(
        int $rowLimit,
        int $offset,
        string $selectQuery,
        string $orderBy,
        string $orderBySuffix="",
): array {
        $orderByString = "ORDER BY ".$orderBy;

        if (in_array($orderBySuffix, ['ASC', 'DESC']) === true) {
            $orderByString .= " ".$orderBySuffix;
        }

        $connexion = $this->getConnection();
        // Set this attribute, so we can use integer parameter in PDO execute function
        // For offset and limit parameters.
        $connexion->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);

        $statement = $connexion->prepare(
            "SELECT *
                       FROM (
                            ".$selectQuery."
                            LIMIT :limitParam OFFSET :offsetParam
                        ) p
                    ".$orderByString
        );
        $statement->execute(
            [
             ':limitParam'  => $rowLimit,
             ':offsetParam' => $offset,
            ]
        );

        $result = $statement->fetchAll();
        $statement->closeCursor();

        return $result;

    }//end execQueryWithLimit()


}//end class
