<?php /** @noinspection PhpUndefinedFieldInspection */

class paymentCartActions extends noxThemeActions {

    /**
     * @var paymentPaypalModel
     */
    public $model;
    /**
     * @var paymentModel
     */
    public $paymentModel;

    private $post = null;

    private static $opt = [
        'top' => 9
    ];

    public function execute() {
        $this->model = new paymentPaypalModel();
        $this->paymentModel = new paymentModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($raw = file_get_contents('php://input')) {
                $this->post = json_decode($raw);
            }
        }
        return parent::execute();
    }

    public function actionCart() {
        $this->caption = 'Shopping cart';
        $this->addVar('vueSpecial', true);
    }

    public function actionCartDetails() {
        $vectorModel = new printsVectorModel();
        $cart = noxSystem::$cart->getCartDetails();
        $items = $vectorModel->where(['id' => array_keys($cart)])->fetchAll();
        foreach ($items as &$v){
            $v['url'] = Prints::createUrlForItem($v, Prints::VECTOR);
            $v['top'] = isset($cart[$v['id']]['top']) ? self::$opt['top'] : 0;
        }
        $res = [
            'status' => 200,
            'data' => $items,
            'auth' => noxSystem::authorization(),
            'days' => noxSystem::$prepayMaxDays
        ];
        echo json_encode($res);
        return 200;
    }

    public function actionCartCmd() {
        if($this->post){
            switch ($this->post->cmd){
                case 'addItem':
                    $this->addItemToCart($this->post->ind);
                    break;
                case 'editItem':
                    $this->editCartItem($this->post->ind, $this->post->prm);
                    break;
                case 'emptyCart':
                    noxSystem::$cart->setCartDetails([]);
                    break;
                case 'removeItem':
                    $this->removeItemFromCart($this->post->ind);
                    break;
            }
        }
        $this->actionCartDetails();
    }

    public function addItemToCart($id, $uid = 0){
        $cart = noxSystem::$cart->getCartDetails();
        if(!isset($cart[$id])){
            $cart[$id] = [];
        }
        noxSystem::$cart->setCartDetails($cart);
    }

    public function editCartItem($indArr, $prm = [], $uid = 0){
        $cart = noxSystem::$cart->getCartDetails();
        if(!is_array($indArr)){
            $indArr = [$indArr];
        }
        $set = isset($prm->top) ? ['top' => self::$opt['top']] : [];
        foreach ($indArr as $ind){
            if(isset($cart[$ind])){
                $cart[$ind] = $set;
            }
        }
        noxSystem::$cart->setCartDetails($cart);
    }

    public function removeItemFromCart($id, $uid = 0){
        $cart = noxSystem::$cart->getCartDetails();
        unset($cart[$id]);
        noxSystem::$cart->setCartDetails($cart);
    }
}
