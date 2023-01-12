<?php

class systemOperActions extends noxThemeActions {

    public $theme = 'administrator';
    public $cache = false;
    public $caption = 'Операции';
    private $cnf;
    private $post = null;

    public function execute() {
        if (!$this->haveRight('control')) {
            return 401;
        }
        $this->cnf = noxConfig::getConfig();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($raw = file_get_contents('php://input')) {
                $this->post = json_decode($raw);
            }
        }
        return parent::execute();
    }

    public function actionDefault() {}

    public function actionCmd(){
        if($this->post){
            switch ($this->post->cmd){
                case 'upRastersDate':
                    $sql = 'UPDATE `prints_blueprint` SET `update_date`= NOW() WHERE 1';
                    break;
                case 'upRastersVer':
                    $this->upRasterVer(1000);
                    break;
                case 'upRequestsDate':
                    $sql = 'UPDATE `prints_request_vector` SET `update_date`= NOW() WHERE 1';
                    break;
                case 'upVectorsDate':
                    $sql = 'UPDATE `prints_vector` SET `update_date`= NOW() WHERE 1';
                    break;
            }
            if(isset($sql)){
                $dbQuery = new noxDbQuery();
                $dbQuery->exec($sql);
            }
        }
        $this->actionData();
    }

    public function actionData(){
        $res = [
            'data' => [
                ['name' => 'Обновить дату векторов', 'cmd' => 'upVectorsDate'],
                ['name' => 'Обновить дату растров', 'cmd' => 'upRastersDate'],
                ['name' => 'Обновить дату реквестов', 'cmd' => 'upRequestsDate'],
                ['name' => 'Добавить версии для растров', 'cmd' => 'upRastersVer'],
            ],
            'test' => $this->test1(),
            'status' => 200
        ];
        echo json_encode($res);
        return 200;
    }

    private function upRasterVer($c){
        $model = new printsBlueprintModel();
        while ($c-- && ($name = $model->where(['ver' => '0'])->limit(1)->fetch('full_name'))){
            $index = (int)$model->reset()->where([
                'full_name' => $name,
                'ver' => ['begin' => 1]
            ])->order('`ver` DESC')->limit(1)->fetch('ver') + 1;
            $rArr = $model->reset()->where([
                'full_name' => $name,
                'ver' => '0'
            ])->fetchAll(null, 'id');
            foreach ($rArr as $id){
                $model->updateByField('id', $id, ['ver' => $index++]);
            }
        }
    }

    private function test1(){
        $modelVectors = new printsVectorModel();
        $v = $modelVectors->where(['prepay' => '0'])->limit(1)->fetch();
        $filePath = str_replace('test.getoutlines.com', 'getoutlines.com', noxRealPath($v['preview']));
//        try{
//            $res = file_exists($filePath);
//        } catch (Exception $e){
//            $res = $e->getMessage();
//        }
        return $filePath;
    }

}
