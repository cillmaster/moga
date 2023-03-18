<?php

class paymentModel extends noxModel {

    public $table = 'payment';

    public function isBuyByUser($type, $id, $userId = false) {
        if(!$userId) $userId = noxSystem::getUserId();

        return $this->where(['user_id' => $userId, /*'purchase_type' => $type,*/ 'purchase_id' => $id, 'status' => 'approved'])->fetch();
    }

    public function isEditor($id) {
        if(!noxSystem::haveRight('payment', 'preorders')) return false;
        if(!($purchaseMaxPrice = $this->select('price')->where([
            'purchase_id' => $id,
            'status' => 'approved'
        ])->order('`price` DESC')->limit(1)->fetch('price'))) return false;
        if(!($vectorPrice = ((new printsVectorModel())->getById($id))['price'])) return false;
        return (float)$purchaseMaxPrice > (float)$vectorPrice;
    }

    public function getAllPurchases() {
        return $this->reset()->where(['status' => 'approved'])->order('datetime DESC')->limit(100)->fetchAll();
    }

    public function getUsersByPurchases($offset, $step) {
        return $this->reset()->select('`user_id`, count(`id`) c, sum(`price`) s')->where(['status' => 'approved'])
            ->group('user_id')->order('c DESC')->limit($offset, $step)->fetchAll('user_id');
    }

    public function getCountPurchases() {
        return $this->reset()->where(['status' => 'approved'])->count();
    }

    public function getCountPreorders() {
        return $this->reset()->where(['status' => 'approved'])->count();
    }

    public function sendCartInvoice($keys){
        if(!$keys) return false;
        if(!($payment = $this->where([
            'id' => $keys,
            'status' => 'approved'
        ])->fetchAll())) return false;
        if(!($user = (new noxUserModel)->getById($payment['user_id']))) return false;
        $strMail = str_replace(['.', '@'], ['&#8291;.&#8291;', '&#8291;@&#8291;'], $user['email']);
        $vectorModel = new printsVectorModel();
        $items = [];
        $total = 0;
        foreach ($payment as $item){
            if($vector = $vectorModel->getById($item['purchase_id'])){
                $items[] = [
                    'name' => $item['purchase_name']
                        . (((float)$item['price'] > (float)$vector['price']) ? ' (+Top View)' : ''),
                    'qty' => 1,
                    'price' => number_format($item['price'], 2)
                ];
                $total += (float)$item['price'];
            }
        }
        if(!$items) return false;
        $paymentPrm = json_decode($payment[0]['_debug_raw_response']);
        $data = [
            'num' => $paymentPrm->transactions[0]->invoice_number,
            'dt' => date('j M Y', strtotime($paymentPrm->create_time)),
            'user' => [
                'name' => $user['name'] ? $user['name'] : $strMail,
                'email' => $strMail
            ],
            'items' => $items,
            'total' => number_format($total, 2)
        ];
        $prm = [
            'subject' => 'Invoice',
            'from' => 'noreply',
            'data' => $data,
            'UserID' => $user['id'],
            'tag' => 'invoice'
        ];
        foreach(['pay@getoutlines.com', $user['email']] as $email){
            $prm['to'] = $email;
            (new postmarkMailer('invoice'))->mail($prm);
        }
    }

    public function sendInvoice($id){
        if(!(int)$id) return false;
        if(!($payment = $this->where([
            'id' => $id,
            'status' => 'approved'
        ])->fetch())) return false;
        if(!($user = (new noxUserModel)->getById($payment['user_id']))) return false;
        $strMail = str_replace(['.', '@'], ['&#8291;.&#8291;', '&#8291;@&#8291;'], $user['email']);
        if(!($vector = (new printsVectorModel)->getById($payment['purchase_id']))) return false;
        $optionTop = (float)$payment['price'] > (float)$vector['price'];
        $paymentPrm = json_decode($payment['_debug_raw_response']);
        $data = [
            'num' => $paymentPrm->transactions[0]->invoice_number,
            'dt' => date('j M Y', strtotime($paymentPrm->create_time)),
            'user' => [
                'name' => $user['name'] ? $user['name'] : $strMail,
                'email' => $strMail
            ],
            'items' => [
                [
                    'name' => $payment['purchase_name'] . ($optionTop ? ' (+Top View)' : ''),
                    'qty' => 1,
                    'price' => number_format($payment['price'], 2)
                ]
            ],
            'total' => number_format($payment['price'], 2)
        ];
        $prm = [
            'subject' => 'Invoice',
            'from' => 'noreply',
            'data' => $data,
            'UserID' => $user['id'],
            'tag' => 'invoice'
        ];
        foreach(['pay@getoutlines.com', $user['email']] as $email){
            $prm['to'] = $email;
            (new postmarkMailer('invoice'))->mail($prm);
        }
    }
}
