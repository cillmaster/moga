<?php

class printsSetsActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsSetModel
     */
    public $model;


    public $caption = 'Сеты векторов';

    public function execute()
    {
        if (!$this->haveRight('vector')) {
            return 401;
        }
        $this->model = new printsSetModel();

        return parent::execute();
    }

    public function actionDefault() {
        $this->addVar('res', $this->model->order('name_full')->fetchAll());
    }
}
