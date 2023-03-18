<?

namespace nox\helpers;

interface PaginatorDatasource {

    function getPageList($offset, $count);
    function getItemsCount();
}
