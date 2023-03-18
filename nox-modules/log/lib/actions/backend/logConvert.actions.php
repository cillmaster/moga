<?php

class logConvertActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    public function actionVectorurl() {
        $m = new printsCategoryModel();
        $s = new printsSetModel();
        $v = new printsVectorModel();
        $m->exec('SELECT COUNT( name ) AS c, id FROM  `prints_vector` GROUP BY name ORDER BY c DESC ');

        $res = $m->fetchAll();
        foreach($res as $ar) {
            if($ar['c'] > 1) {
                $s->updateSets($v->getById($ar['id']));
            }
        }
    }
}