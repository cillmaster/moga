<?php
/**
 * Select options of Make for category_id
 *
 * @version    1.0
 * @package    prints
 */

class printsMakeOptionsAction extends noxAction
{
    public function execute(){
        $category_id = $_GET['category_id'];
        $make_id = $_GET['make_id'];

        if($make_id){ // names
            $tmp = new noxDbQuery();
            $tmp->exec('SELECT `db_table` FROM `prints_class` WHERE `id` = ' . $category_id);
            $pref = $tmp->fetch('db_table');
            $tmp = new noxDbQuery();
            $tmp->exec('SELECT \'\' as id, `name` FROM `prints_vector` 
                LEFT JOIN `prints_class_car` ON `prints_vector`.`item_id` = `prints_class_' . $pref .'`.`id` 
                WHERE `make_id` = ' . $make_id . ' GROUP BY `name`');
            $namesDataOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/optionsDatalistMake.html');
            $namesDataOptions->addVar('res', $tmp->fetchAll());

            echo $namesDataOptions->__toString();
        }else{ // makes
            $res = (new printsMakeModel())->where('class_id', $category_id)->select('id, name')->order('name')->fetchAll('id');

            $makesSelectOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/makeOptions.html');
            $makesSelectOptions->addVar('selected', -1);
            $makesSelectOptions->addVar('res', $res);

            $makesDataOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/optionsDatalistMake.html');
            $makesDataOptions->addVar('res', $res);

            echo $makesSelectOptions->__toString() . ':::::' . $makesDataOptions->__toString();
        }
    }
}
