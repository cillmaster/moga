<?php

class paymentPreorderActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var paymentModel
     */
    public $model;

    public function execute()
    {
        if (!$this->haveRight('preorders')) {
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
        $this->title = 'Список предзаказов';
        //$res = $this->model->getAllPurchases();

        $user = noxSystem::$userControl->getUser();

        $tmp = new noxDbQuery();
        $tmp->exec('SELECT `payment`.*, `prepay` FROM `payment` LEFT JOIN `prints_vector` 
            ON `purchase_id` = `prints_vector`.`id` 
            WHERE `status` = \'approved\' 
              AND (`purchase_type` = "prepay" OR `payment`.price > `prints_vector`.price) 
              AND YEAR(`datetime`) > 2017
              AND `datetime` > \'' . $user['registration_date'] . '\'
            ORDER BY `datetime` DESC');
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
                    $nowVector = !(int)$vectors[$item['purchase_id']]['prepay'];
                    $nowFinish = (int)$item['ready'];
                    $oldFinish = $nowVector && $nowFinish;
                    $wasPrepay = $item['purchase_type'] == 'prepay';
                    $baseState = $oldFinish ? 'gray' : ($nowVector ? 'orange' : 'red');
                    if((float)$item['price'] > (float)$vectors[$item['purchase_id']]['price']){
                        $hasTop = $vectors[$item['purchase_id']]['views'] & 2;
                        $optionState = $hasTop ? $baseState : 'red';
                        $baseState = ($nowVector && !$wasPrepay && !$hasTop) ? 'black' : $baseState;
                        $item['option'] = '<span class="' . $optionState . '">[</span>'
                            . ($wasPrepay ? '<span class="' . $optionState . '">делать</span>' : 'готов')
                            . '<span class="' . $optionState . '">+top]</span>&nbsp;';
                        if(!$wasPrepay){
                            $item['download'] = true;
                            if(!$nowFinish){
                                $item['top_ready'] = true;
                            }
                        }
                    } else {
                        $item['option'] = '';
                    }
                    $item['state'] = $baseState;
                    if($nowVector && !$nowFinish){
                        $item['complete'] = true;
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
        $this->addVar('admin', $this->haveRight('control'));
    }
}
