<?php

class paymentPaymentActions extends noxThemeActions {

    /**
     * @var paymentPaypalModel
     */
    public $model;
    /**
     * @var paymentModel
     */
    public $paymentModel;

    public function execute() {
        $this->model = new paymentPaypalModel();
        $this->paymentModel = new paymentModel();
        parent::execute();
    }

    public function actionStartCartBuy(){
        if(noxSystem::authorization()) {
            $cart = noxSystem::$cart->getCartDetails();
            $ar = $this->paymentModel->getByField([
                'purchase_id' => array_keys($cart),
                'user_id' => noxSystem::getUserId(),
                'status' => 'approved'
            ]);
            if(!$ar) {
                switch(getParam(@$_GET['webProfile'], 'login')) {
                    case 'billing':
                        $webProfileId = PAYPAL_WEB_PROFILE_BILLING;
                        break;
                    case 'login':
                        $webProfileId = PAYPAL_WEB_PROFILE;
                        break;
                }
                if(isset($webProfileId)){
                    $this->model->createCartPayment($cart, $webProfileId);
                }
            } else {
                noxSystem::locationBack();
            }
        } else {
            noxSystem::locationBack();
        }
    }

    public function actionStartBuy() {
        $type = $this->getParam('what', '');
        $id = $this->getParam('id', 0);

        if($type === 'vector') {
            $ar = $this->paymentModel->getByField(['purchase_type' => $type, 'purchase_id' => $id, 'user_id' => noxSystem::getUserId(), 'status' => 'approved']);
            if($ar || !noxSystem::authorization()) {
                noxSystem::locationBack();
            }

            $webProfileName = getParam(@$_GET['webProfile'], 'login');
            switch($webProfileName) {
                case 'billing':
                    $webProfileId = PAYPAL_WEB_PROFILE_BILLING;
                    break;
                default:
                    $webProfileId = PAYPAL_WEB_PROFILE;
            }


            $this->model->createPayment([['type' => Prints::VECTOR, 'id' => $id]], $webProfileId);
        }
        else {
            noxSystem::location('/');
        }
    }

    public function actionSaleFinish() {
        if(isset($_GET['type']) && $_GET['type'] === 'paypal') {
            $paymentId = @$_GET['paymentId'];
            $token = @$_GET['token'];
            $payerId = @$_GET['PayerID'];
            $success = @$_GET['success'];

            $e = new PayPal\Api\PaymentExecution();

            $e->setPayerId($payerId);
            $payment = (new \PayPal\Api\Payment())->setId($paymentId);

            if($success == 'false') {
                if(isset($_GET['back_url'])) {
                    noxSystem::location('https://' . noxSystem::$domain . $_GET['back_url']);
                }
                else {
                    noxSystem::location('https://' . noxSystem::$domain);
                }
            }

            try {
                $payment->execute($e, $this->model->getApiContext());

                if($payment->getState() === 'approved') {
                    $payInModel = $this->paymentModel
                        ->getByField(['user_id' => noxSystem::getUserId(), 'payment_id' => $payment->getId()])
                        ->fetchAll('id');
                    $payKeys = array_keys($payInModel);
                    $this->paymentModel->updateById($payKeys,
                        ['_debug_raw_response' => $payment->toJSON(), 'status' => $payment->getState()]
                    );
                    $vectorModel = new printsVectorModel();
                    $success = 0;
                    $vectorSingle = false;
                    foreach ($payInModel as $key => $item){
                        $vector = $vectorModel->getById($item['purchase_id']);
                        if($vector){
                            if(!$vectorSingle){
                                $vectorSingle = $vector;
                                setcookie('purchase_success',
                                    $item['id'] . '::'
                                    . $item['purchase_name'] . '::'
                                    . $item['price'] . '::'
                                    . $item['purchase_type'],
                                    time() + 60 * 60 * 24 * 7, '/');
                                if($item['purchase_type'] == 'prepay'){
                                    setcookie('prepayment_success', 'true', time() + 60 * 60 * 24 * 7, '/');
                                }
                            }
                            $success++;
                            if($item['purchase_type'] === 'prepay'){
                                $optionTop = (float)$item['price'] > (float)$vector['price'];
                                (new kafMailer('new_preorder'))->mail([
                                    'from' => 'noreply',
                                    'to' => ['editor1@getoutlines.com'],
                                    'subject' => 'Приоритетный чертеж - ' . $vector['full_name'],
                                    'link' => 'http://' . noxSystem::$domain .
                                        '/administrator/prints/?section=vector&action=view&id=' . $item['purchase_id'],
                                    'title' => $vector['full_name'],
                                    'option' => $optionTop ? '[делать+top] ' : '',
                                    'deadline' => noxDate::toDateTime(strtotime($item['datetime']) + 86400 * 4)
                                ]);
                            }
                        }
                    }
                    if($success) {
                        if($success == 1){
                            $loc = Prints::createUrlForItem($vectorSingle, Prints::VECTOR) . '?download=true';
                        } else {
                            $loc = '/users/downloads';
                        }
                        $this->paymentModel->sendCartInvoice($payKeys);
                    } else {
                        $loc = '/';
                    }
//                    $vector = (new printsVectorModel)->getById($payInModel['purchase_id']);
//                    if($vector){
//                        setcookie('purchase_success',
//                            $payInModel['id'] . '::'
//                            . $payInModel['purchase_name'] . '::'
//                            . $payInModel['price'] . '::'
//                            . $payInModel['purchase_type']
//                            , time() + 60 * 60 * 24 * 7, '/');
//                        if($payInModel['purchase_type'] === 'prepay'){
//                            setcookie('prepayment_success', 'true', time() + 60 * 60 * 24 * 7, '/');
//                            $details = (new printsVectorModel())->where('id', $payInModel['purchase_id'])->fetch();
//                            $optionTop = (float)$payInModel['price'] > (float)$details['price'];
//                            (new kafMailer('new_preorder'))->mail([
//                                'from' => 'noreply',
//                                'to' => ['editor1@getoutlines.com'],
//                                'subject' => 'Приоритетный чертеж - ' . $details['full_name'],
//                                'link' => 'http://' . noxSystem::$domain .
//                                    '/administrator/prints/?section=vector&action=view&id=' . $payInModel['purchase_id'],
//                                'title' => $details['full_name'],
//                                'option' => $optionTop ? '[делать+top] ' : '',
//                                'deadline' => noxDate::toDateTime(strtotime($payInModel['datetime']) + 86400 * 4)
//                            ]);
//                        }
//                        $this->paymentModel->sendInvoice($payInModel['id']);
//                        $loc = Prints::createUrlForItem($vector, Prints::VECTOR) . '?download=true';
//                    }else{
//                        $loc = '/';
//                    }
                    noxSystem::location($loc);
                }
            } catch(Exception $e) {
                echo 'Some error during processed operation';
                throw new noxException(_d($e, true));
            }
        }
        else {
            //return 404;
        }
    }
}
