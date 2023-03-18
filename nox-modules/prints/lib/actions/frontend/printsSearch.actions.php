<?php

class printsSearchActions extends noxThemeActions {
    public $cache = false;

    public function actionHint(){
        echo json_encode((new printsSetModel())->getNamesByFilter($_GET['q']));
        return 200;
    }
}