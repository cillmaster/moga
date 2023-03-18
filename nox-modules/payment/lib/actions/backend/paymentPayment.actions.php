<?php

class paymentPaymentActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var paymentModel
     */
    public $model;

    public function execute()
    {
        if (!$this->haveRight('sales')) {
            return 401;
        }

        $this->model = new paymentModel();

        return parent::execute();
    }

    public function actionWebProfiles() {
        $this->addVar('profiles', (new paymentPaypalModel())->getWebProfiles());
    }

    public function actionAddWebProfile() {
       // _d((new paymentPaypalModel())->createWebProfile());
    }

    public function actionDefault() {
        $this->title = 'Список покупок';
        //$res = $this->model->getAllPurchases();
        $page = $_GET['page'];
        $this->addVar('pager', (new kafPager())->create($this->model->getCountPurchases(), $onPage = 50, 5));

        $tmp = new noxDbQuery();
        $tmp->exec('SELECT `payment`.*, `prepay` FROM `payment` LEFT JOIN `prints_vector` 
            ON `purchase_id` = `prints_vector`.`id` WHERE `status` IN ("approved", "refund") ORDER BY `datetime` DESC 
            LIMIT ' . ($page - 1) * $onPage . ',' . $onPage);
        $res = $tmp->fetchAll();

        if($res) {
            $vectorsId = [];
            foreach($res as $ar) {
                if($ar['user_id']) {
                    $usersId[$ar['user_id']] = $ar['user_id'];
                }
                if($ar['purchase_type'] === 'vector' || $ar['purchase_type'] === 'prepay') {
                    $vectorsId[$ar['purchase_id']] = $ar['purchase_id'];
                }
            }

            if($vectorsId){
                $vectors = (new printsVectorModel())->where('id', $vectorsId)->fetchAll('id');
                foreach ($res as &$item){
                    if((float)$item['price'] > (float)$vectors[$item['purchase_id']]['price']){
                        $item['option'] = '<span class="red">[</span>'
                            . (($item['purchase_type'] === 'prepay') ? '<span class="red">делать</span>' : 'готов')
                            . '<span class="red">+top]</span>&nbsp;';
                    } else {
                        $item['option'] = '';
                    }
                }
                $this->addVar('vectors', $vectors);
            }
        }

        $this->addVar('res', $res);

        if(!empty($usersId)) {
            $userModel = noxSystem::getUserModel()->reset();
            $users = $userModel->where('id', $usersId)->fetchAll('id');
        }
        else {
            $users = false;
        }
        $this->addVar('users', $users);
    }

    public function actionRefund(){
        if(!$this->haveRight('sales')){
            return 401;
        }
        $id = $_GET['id'];
        $status = $_GET['status'];
        if(!$id || !$status){
            return 400;
        }

        $this->model->updateById($id, ['status' => $status]);

        if ($this->ajax()) {
            return 200;
        } else {
            noxSystem::location('?section=payment');
        }
    }
}
