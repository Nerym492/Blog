<?php

namespace App\Controllers;

use App\EntityManager\CommentManager;
use App\EntityManager\PostManager;
use Pagination\Pagination;
use Pagination\StrategySimple;

abstract class AbstractController
{
    protected PostManager $postManager;
    protected CommentManager $commentManager;

    public function __construct()
    {
        $this->postManager = new PostManager();
        $this->commentManager = new CommentManager();
    }

    /**
     * @param int $nbRows Total number of rows given by the query
     * @param int $limitPerPage Number of lines per page
     * @param int $activePage Active page when the pagination is loaded
     * @return array Contains all the elements of the pagination (firstPage, lastPage, previousPage, nextPage, activePage,
     * iterator)
     */
    protected function getPagination(int $nbRows, int $limitPerPage, int $activePage): array
    {
        //use pagination class with results, per page and page
        if ($nbRows > 0) {
            $pagination = new Pagination($nbRows, $limitPerPage, $activePage);
            //get indexes in page
            //StrategySimple(param = number of pages visible in the pagination)
            $indexes = $pagination->getIndexes(new StrategySimple(5));
            $pagesNumbers = [
                'firstPage' => $pagination->getFirstPage(),
                'lastPage' => $pagination->getLastPage(),
                'previousPage' => $pagination->getPreviousPage(),
                'nextPage' => $pagination->getNextPage(),
                'activePage' => $pagination->getPage(),
                'iterator' => $indexes->getIterator()
            ];
        } else {
            $pagesNumbers = [
                'firstPage' => 1,
                'lastPage' => 1,
                'previousPage' => 1,
                'nextPage' => 1,
                'activePage' => 1,
                'iterator' => 1
            ];
        }


        return $pagesNumbers;
    }
}