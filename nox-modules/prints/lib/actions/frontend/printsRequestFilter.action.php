<?php
/**
 * Результат поиска реквестов по параметрам
 *
 * @version    1.0
 * @package    prints
 */

class printsRequestFilterAction extends noxAction
{
    public $requestVectorModel;

    public function execute(){
        $this->requestVectorModel = new printsRequestVectorModel();
        $requestSearch = new noxTemplate($this->moduleFolder . '/templates/frontend/requestSearch.html');

        if($prm = $this->requestVectorModel->getSearchModel()){
            $this->requestVectorModel->where($this->requestVectorModel->getSearchWhere($prm));
        }

        $page = $_GET['page'];
        $requestSearch->addVar('pager', (new kafPager('pager2.html', '/requests'))->create2($this->requestVectorModel->count(), $onPage = 50, 5, $page));

        $res = $this->requestVectorModel->getRequestsList($onPage, ($page - 1) * $onPage);
        $requestSearch->addVar('res', $res);
        echo $requestSearch->__toString();
    }
}
