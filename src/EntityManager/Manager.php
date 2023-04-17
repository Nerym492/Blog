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


    /**
     * @param int    $rowsLimit Number of rows per page.
     * @param int    $pageNum   Page currently being read
     * @param int    $rowsCount Total number of lines available in the pagination
     * @param string $orderBy   Ascending or descending order
     * @return array
     */
    public static function calcPageAndOffset(int $rowsLimit, int $pageNum, int $rowsCount, string $orderBy="ASC"): array
    {
        // Order by ASC.
        if ($orderBy !== "DESC") {
            $offset = (($rowsLimit * $pageNum) - $rowsLimit);

            // The last remaining article on the page has been deleted.
            if ($offset !== 0 && ($offset % $rowsCount) === 0) {
                $pageNum--;
                $offset -= $rowsLimit;
            }
        }

        if ($orderBy === "DESC") {
            $offset = ($rowsCount - ($rowsLimit * $pageNum));
            if ($offset < 0) {
                $recalcOffset = ($offset + $rowsLimit);
                // The last remaining article on the page has been deleted.
                if ($recalcOffset === 0) {
                    $pageNum--;
                }

                // The rowLimit is recalculated because there is not enough lines.
                if ($recalcOffset < 0 || $recalcOffset > 0) {
                    $rowsLimit = ($offset + $rowsLimit);
                }

                $offset = 0;
            }
        }

        return [
                'offset'    => $offset,
                'pageNum'   => $pageNum,
                'rowsLimit' => $rowsLimit,
               ];

    }//end calcPageAndOffset()


}//end class
