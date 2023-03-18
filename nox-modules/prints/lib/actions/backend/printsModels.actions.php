<?php

class printsModelsActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsSetModel
     */
    public $model;


    public $caption = 'Модели векторов';

    public function execute()
    {
        if (!$this->haveRight('vector')) {
            return 401;
        }
        $this->model = new printsVectorModel();

        return parent::execute();
    }

    public function actionDefault() {
        $res = [];
        if(isset($_GET['filter'])){
            if($make = (int)$_GET['filter']['make_id']){
                $where = '`prints_vector`.`item_id` IN(select id from `prints_class_car` where make_id = ' . $make . ')';
                $dbQuery = new noxDbQuery();
                $sql = 'SELECT 
                    CONCAT_WS(\' \', `prints_make`.`name`, `prints_vector`.`name`) name, 
                    COUNT(`prints_vector`.`id`) total,
                    MAX(`prints_vector`.`added_date`) last
                FROM `prints_vector` 
                LEFT JOIN `prints_class_car` ON `prints_vector`.`item_id` = `prints_class_car`.`id`
                LEFT JOIN `prints_make` ON `prints_class_car`.`make_id` = `prints_make`.`id`
                WHERE (`prints_vector`.`class_id` = 1) AND ' . $where
                . ' GROUP BY name ASC';
                $dbQuery->exec($sql);
                $res = $dbQuery->fetchAll();
            }
        }
        $this->addVar('makes', (new printsMakeModel())->getAllByCategory(1));
        $this->addVar('res', $res);
    }
}
