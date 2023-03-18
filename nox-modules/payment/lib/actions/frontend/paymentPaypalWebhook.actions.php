<?php

class paymentPaypalwebhookActions extends noxActions {

    public $model;

    public function __construct() {
        if($this->getParam('urlKey', '') !== paymentPaypalModel::URL_KEY) {
            return 404;
        }

        $this->model = new paymentPaypalModel();
        parent::__construct();
    }

    public function actionSaleCompleted() {

    }
}