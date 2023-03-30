<?php

namespace App\Controllers;

use Pagination\Pagination;
use Pagination\StrategySimple;
use \Twig\Environment as TwigEnv;
use Twig\Extension\DebugExtension as TwigDebug;
use Twig\Loader\FilesystemLoader as TwigLoader;

abstract class AbstractController
{
    /**
     * @param int $nbRows Total number of rows given by the query
     * @param int $nbMaxPages Number of pages in the pagination
     * @param int $activePage Active page when the pagination is loaded
     * @return array Contains all the elements of the pagination (firstPage, lastPage, previousPage, nextPage, activePage,
     * iterator)
     */
    protected function getPagination(int $nbRows, int $nbMaxPages, int $activePage): array
    {
        //use pagination class with results, per page and page
        $pagination = new Pagination($nbRows, $nbMaxPages, $activePage);
        //get indexes in page
        //StrategySimple(param = number of pages visible in the pagination)
        $indexes = $pagination->getIndexes(new StrategySimple(5));

        return [
            'firstPage' => $pagination->getFirstPage(),
            'lastPage' => $pagination->getLastPage(),
            'previousPage' => $pagination->getPreviousPage(),
            'nextPage' => $pagination->getNextPage(),
            'activePage' => $pagination->getPage(),
            'iterator' => $indexes->getIterator()
        ];
    }
}