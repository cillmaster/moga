<?php

class paymentAdministratorActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var paymentPaypalModel
     */
    public $model;

    public function execute()
    {
        if (!$this->haveRight('control')) {
            return 401;
        }

        $this->model = new paymentPaypalModel();

        return parent::execute();
    }

    public function actionDefault() {

    }

    public function actionWebProfiles() {
        _d($this->model->getWebProfiles());
    }



}
