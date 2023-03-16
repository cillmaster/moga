<?

namespace nox\helpers;

class Paginator {

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $pages;

    /**
     * @var int
     */
    private $onPage;

    /**
     * @var PaginatorDatasource
     */
    private $dataSource;

    private $data = null;

    public function __construct(PaginatorDatasource $dataSource, $onPage = 200, $page = null)
    {
        $this->dataSource = $dataSource;
        $this->onPage = $onPage;
        $this->page =
            ($page === null || !is_numeric($page))
                ? ((empty($_GET['page']) || !is_numeric($_GET['page'])) ? 1 : (int)$_GET['page'])
                : $page;

        $this->pages = ceil($this->dataSource->getItemsCount() / $this->onPage);
    }

    public function hasItems()
    {
        $this->loadDataIfNeed();
        return count($this->data) > 0;
    }

    public function getItems()
    {
        $this->loadDataIfNeed();
        return $this->data;
    }

    public function getPage() {
        return $this->page;
    }

    public function getPagesCount() {
        return $this->pages;
    }

    public function getNavigator() {
        $res = array();
        $n = $this->page;
        $c = $this->pages;
        if($n > 2){
            $res[0] = array(
                'val' => 1,
                'type' => 'link'
            );
        };
        if($n > 3){
            $res[1] = array(
                'val' => '...',
                'type' => 'delimiter'
            );
        };
        if($n > 1){
            $res[2] = array(
                'val' => $n - 1,
                'type' => 'link'
            );
        };
        $res[3] = array(
            'val' => $n,
            'type' => 'active'
        );
        if($c - $n > 0){
            $res[4] = array(
                'val' => $n + 1,
                'type' => 'link'
            );
        };
        if($c - $n > 2){
            $res[5] = array(
                'val' => '...',
                'type' => 'delimiter'
            );
        };
        if($c - $n > 1){
            $res[6] = array(
                'val' => $c,
                'type' => 'link'
            );
        };
        return $res;
    }

    private function loadDataIfNeed() {
        if($this->data === null) {
            $this->data = $this->dataSource->getPageList(($this->page - 1) * $this->onPage, $this->onPage);
        }
    }
}
