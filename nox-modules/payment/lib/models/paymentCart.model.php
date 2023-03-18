<?php

class paymentCartModel extends noxModel {

    public $table = 'carts';
    private static $cartVersion = 1;

    public function getCartDetails($uid = 0){
        if($uid = $uid ? $uid : noxSystem::getUserId()){
            return $this->decodeCart($this->getCartDetailsByUid($uid));
        } else {
            return $this->decodeCart($this->getCartDetailsByKey());
        }
    }

    private function getCartDetailsByUid($uid){
        if(!($res = $this->where('c_uid', $uid)->fetch())){
            $res = '';
            $this->initCart('uid', $uid);
        } else {
            $res = $res['c_items'];
        }
        return $res;
    }

    private function getCartDetailsByKey(){
        if(!isset($_COOKIE['ol_key']) || !($res = $this->where('c_key', $_COOKIE['ol_key'])->fetch())){
            $key = $this->genUniqueKey();
            $res = '';
            $this->initCart('key', $key);
        } else {
            $res = $res['c_items'];
        }
        return $res;
    }

    public function mergeCartDetails(){
        if(!($uid = noxSystem::getUserId())) return;
        $cartById = $this->decodeCart($this->getCartDetailsByUid($uid));
        $cartByKey = $this->decodeCart($this->getCartDetailsByKey());
        foreach ($cartByKey as $key => $value){
            if(!isset($cartById[$key])){
                $cartById[$key] = $cartByKey[$key];
            } elseif(!isset($cartById[$key]['top']) && isset($cartByKey[$key]['top'])){
                $cartById[$key]['top'] = $cartByKey[$key]['top'];
            }
        }
        $this->setCartDetailsByKey('');
        $this->setCartDetailsByUid($this->encodeCart($cartById), $uid);
    }

    public function initCart($type, $value, $items = ''){
        return $this->insert([
            'c_items' => $items,
            'c_' . $type => $value,
            'c_dt' => time()
        ]);
    }

    private function genUniqueKey(){
        $key = gen(32);
        while($this->where('c_key', $key)->fetch()){
            $key = gen(32);
        }
        setcookie('ol_key', $key, time() + 86400 * 30, '/', noxConfig::getConfig()['host'], 1);
        return $key;
    }

    public function setCartDetails($items, $uid = 0){
        $items = $this->encodeCart($items);
        if($uid = $uid ? $uid : noxSystem::getUserId()){
            $this->setCartDetailsByUid($items, $uid);
        } else {
            $this->setCartDetailsByKey($items);
        }
    }

    private function setCartDetailsByUid($items, $uid){
        if($this->where('c_uid', $uid)->fetch()){
            $this->updateByField('c_uid', $uid, [
                'c_items' => $items,
                'c_dt' => time()
            ]);
        } else {
            $this->initCart('uid', $uid, $items);
        }
    }

    private function setCartDetailsByKey($items){
        if(!isset($_COOKIE['ol_key']) || !$this->where('c_key', $_COOKIE['ol_key'])->fetch()){
            $key = $this->genUniqueKey();
            $this->initCart('key', $key, $items);
        } else {
            $this->updateByField('c_key', $_COOKIE['ol_key'], [
                'c_items' => $items,
                'c_dt' => time()
            ]);
        }
    }

    public function decodeCart($str = ''){
        if($str){
            $tmp = explode('~', $str);
            $decoder = 'decodeCart_' .$tmp[0];
            return $this->$decoder($tmp[1]);
        } else {
            return [];
        }
    }

    private function decodeCart_1($str = ''){
        $res = [];
        foreach (explode('||', $str) as $item){
            $tmp = explode('|', $item);
            $res[$tmp[0]] = (int)$tmp[1] ? ['top' => (int)$tmp[1]] : [];
        }
        return $res;
    }

    public function encodeCart($prm = []){
        $encoder = 'encodeCart_' . self::$cartVersion;
        return $prm ? join('~', [self::$cartVersion, $this->$encoder($prm)]) : '';
    }

    private function encodeCart_1($prm = []){
        $res = [];
        foreach ($prm as $key => $value){
            $res[] = join('|', [$key, isset($value['top']) ? $value['top'] : 0]);
        }
        return join('||', $res);
    }
}
