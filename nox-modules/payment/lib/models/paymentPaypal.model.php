<?php

use \PayPal\Api\Amount;
use \PayPal\Api\Item;
use \PayPal\Api\ItemList;
use \PayPal\Api\Payer;
use \PayPal\Api\Payment;
use \PayPal\Api\RedirectUrls;
use \PayPal\Api\Transaction;

class paymentPaypalModel {

    private $apiContext;

    private $vectorModel;

    public function __construct() {
        $this->apiContext = include_once noxRealPath('/nox-modules/3rdparty/PayPal/bootstrap.php');
    }

    public function getApiContext() {
        return $this->apiContext;
    }

    public function createCartPayment($cart, $webProfileId = PAYPAL_WEB_PROFILE) {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $paypalItems = [];
        $totalAmount = 0;

        $paymentData = [];

        $this->vectorModel = new printsVectorModel();
        $vectors = $this->vectorModel->where('id', array_keys($cart))->fetchAll();

        foreach($vectors as $vector) {
            $item = new Item();
            $item->setCurrency('USD')->setQuantity(1);
            $payExtra = 0;
            foreach ($cart[$vector['id']] as $key => $value){
                $payExtra += $value;
            }
            $vector['price'] = (float)$vector['price'] + $payExtra;
            $totalAmount += $vector['price'];
            $item->setName($vector['full_name'] . ' vector drawings')->setPrice($vector['price']);
            $paypalItems[] = $item;
            $paymentData[] = [
                'price' => $vector['price'],
                'purchase_type' => $vector['prepay'] ? 'prepay' : 'vector',
                'ready' => ($vector['prepay'] || $payExtra) ? 0 : 1,
                'purchase_id' => $vector['id'],
                'purchase_name' => $vector['full_name'],
                'status' => 'sale_wait',
                'user_id' => noxSystem::getUserId(),
            ];
        }

        if(!@$paypalItems) {
            return false;
        }

        $itemList = new ItemList();
        $itemList->setItems($paypalItems);

        $amount = new Amount();
        $amount->setCurrency("USD")->setTotal($totalAmount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Outlines vector payment")
            ->setInvoiceNumber(uniqid(true));

        $baseUrl = getBaseUrl();
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/payments/finish?type=paypal&success=true")
            ->setCancelUrl("$baseUrl/payments/finish?type=paypal&success=false&back_url=" . Prints::createUrlForItem($vector, Prints::VECTOR));

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction))
            ->setExperienceProfileId($webProfileId);

        $request = clone $payment;

        try {
            $payment->create($this->apiContext);
            $approvalUrl = $payment->getApprovalLink();
            foreach($paymentData as &$ar) {
                $ar['payment_id'] = $payment->getId();
            }
            (new paymentModel())->insert($paymentData);
            noxSystem::$cart->setCartDetails([]);
            noxSystem::location($approvalUrl);
        } catch (Exception $ex) {
            if(noxConfig::isDebug()) {
                ResultPrinter::printOutput("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", $payment, $request, null);
                _d($ex);
            }
            else {
                noxSystem::locationBack();
            }
        }
    }

    public function createPayment($items, $webProfileId = PAYPAL_WEB_PROFILE) {
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $paypalItems = [];
        $totalAmount = 0;

        $paymentData = [];

        $this->vectorModel = new printsVectorModel();

        $payExtra = [];
        if(isset($_COOKIE['pay_real_price'])) {
            $tmp =  explode('::', $_COOKIE['pay_real_price']);
            $payExtra[$tmp[0]] = (float)$tmp[1];
            setcookie('pay_real_price', '', time() - 1000, '/');
        }
        foreach($items as $p) {
            $item = new Item();
            $item->setCurrency('USD')->setQuantity(1);

            if($p['type'] === Prints::VECTOR) {
                $vector = $this->vectorModel->getById($p['id']);
                if($vector) {
                    $vector['price'] = (float)$vector['price'] + (isset($payExtra[$p['id']]) ? $payExtra[$p['id']] : 0);
                    $totalAmount += $vector['price'];
                    $item->setName($vector['full_name'] . ' vector drawings')->setPrice($vector['price']);
                    $paypalItems[] = $item;
                    $paymentData[] = [
                        'price' => $vector['price'],
                        'purchase_type' => $vector['prepay'] ? 'prepay' : 'vector',
                        'ready' => ($vector['prepay'] || isset($payExtra[$p['id']])) ? 0 : 1,
                        'purchase_id' => $vector['id'],
                        'purchase_name' => $vector['full_name'],
                        'status' => 'sale_wait',
                        'user_id' => noxSystem::getUserId(),
                    ];
                }
            }
        }

        if(!@$paypalItems) {
            noxSystem::location('/');
        }

        $itemList = new ItemList();
        $itemList->setItems($paypalItems);

        $amount = new Amount();
        $amount->setCurrency("USD")->setTotal($totalAmount);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription("Outlines vector payment")
            ->setInvoiceNumber(uniqid(true));

        $baseUrl = getBaseUrl();
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl("$baseUrl/payments/finish?type=paypal&success=true")
            ->setCancelUrl("$baseUrl/payments/finish?type=paypal&success=false&back_url=" . Prints::createUrlForItem($vector, Prints::VECTOR));

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction))
            ->setExperienceProfileId($webProfileId);


        $request = clone $payment;

        try {
            $payment->create($this->apiContext);
            $approvalUrl = $payment->getApprovalLink();
            foreach($paymentData as &$ar) {
                $ar['payment_id'] = $payment->getId();
            }
            (new paymentModel())->insert($paymentData);
            noxSystem::location($approvalUrl);
            //ResultPrinter::printOutput("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", $payment, $request, null);
        } catch (Exception $ex) {
            if(noxConfig::isDebug()) {
                ResultPrinter::printOutput("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", $payment, $request, null);
                _d($ex);
            }
            else {
                noxSystem::locationBack();
            }
        }

        return $payment;
    }

    public function createWebProfile() {
        $webProfile = new \PayPal\Api\WebProfile();
        $flowConfig = new \PayPal\Api\FlowConfig();
        $inputConfig = new \PayPal\Api\InputFields();
        $presentationConfig = new \PayPal\Api\Presentation();

        $flowConfig->setLandingPageType('billing');

        $inputConfig->setAllowNote(true)
                    ->setNoShipping(1);

        $presentationConfig->setBrandName('Outlines')
            ->setLocaleCode('GB')
            ->setLogoImage('https://getoutlines.com/nox-themes/default/images/paypal__logo.png');

        $webProfile->setFlowConfig($flowConfig)
            ->setInputFields($inputConfig)
            ->setPresentation($presentationConfig)
            ->setName('Outlines - billing');

        _d($webProfile);
        return $webProfile->create($this->apiContext);
    }

    public function getWebProfiles() {
        $webProfile = new \PayPal\Api\WebProfile();
        return $webProfile->get_list($this->apiContext);
    }
}
