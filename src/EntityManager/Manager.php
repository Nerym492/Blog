<?php

namespace App\EntityManager;

use App\Lib\DatabaseConnection;
use App\Lib\Environment;
use App\Lib\Session;
use PDO;

abstract class Manager
{
    /**
     * @var PDO|null PDO object
     */
    protected ?PDO $database;

    /**
     * @var Session Session variables
     */
    protected Session $session;

    /**
     * @var Environment Environment variables
     */
    protected Environment $env;

    /**
     * Create a connection to the database
     *
     * @param Session     $session Session variables
     * @param Environment $env     Environment variables
     */
    public function __construct(Session $session, Environment $env)
    {
        $this->session = $session;
        $this->env = $env;
        $this->database = DatabaseConnection::getInstance($session, $env)->getConnection();

    }//end __construct()


    public static function calcPageAndOffset(int $rowsLimit, int $pageNum, int $rowsCount, string $sortOrder = ""): array
    {
        if ($sortOrder === "DESC") {
            $offset = $rowsCount - ($rowsLimit * $pageNum);
            if ($offset < 0) {
                if ($offset + $rowsLimit === 0) {
                    $pageNum--;
                } else {
                    $rowsLimit = $offset + $rowsLimit;
                }
                $offset = 0;
            }
        } else {
            $offset = ($rowsLimit * $pageNum) - $rowsLimit;
        }

        /*
            The pagination will display the previous page with 0 line if the current page is empty
            Example 4 rows total but 2 pages with 4 rows per page (page 2 empty)
            So we need one less page and to start at the beginning of this one
        */

        if ($offset !== 0 && $offset % $rowsCount === 0) {
            $pageNum--;
            $offset -= $rowsLimit;
        }

        return [
                'offset'    => $offset,
                'pageNum'   => $pageNum,
                'rowsLimit' => $rowsLimit,
               ];

    }//end calcPageAndOffset()

}//end class

