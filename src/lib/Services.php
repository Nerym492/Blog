<?php

namespace App\Lib;

abstract class Services
{
    public static function calcPageAndOffset(int $rowsLimit, int $pageNum, $rowsCount): array
    {
        $offset = ($rowsLimit * $pageNum) - $rowsLimit;
        /* The pagination will display the previous page with 0 line if the current page is empty
           Example 4 rows total but 2 pages with 4 rows per page (page 2 empty)
           So we need one less page and to start at the beginning of this one*/
        if ($offset !== 0 && $offset % $rowsCount === 0) {
            $pageNum--;
            $offset -= $rowsLimit;
        }

        return ['offset' => $offset, 'pageNum' => $pageNum];
    }
}