<?php

class printsRequestVectorModel extends noxModel
{
    public $table = 'prints_request_vector';

    private static $salt = 'Hfrp4iv3ll3';

    public static function generateSecretCodeForRequest($request) {
        return hash('sha256', self::$salt . $request['id'] . $request['request_date'] . ($request['id'] + 13) . self::$salt);
    }

    public function checkRequestCodes($userId, $live = false) {
        if(isset($_COOKIE['myrequests'])) {
            $myrequests = explode('#.#', $_COOKIE['myrequests']);
            $requestVoteModel = new printsRequestVoteModel();
            foreach($myrequests as $req) {
                $data = explode('::', $req);
                if(count($data) >= 2) {
                    if($data[1] === self::generateSecretCodeForRequest($this->getById($data[0]))) {
                        $this->updateById($data[0], ['user_id' => $userId]);
                        $requestVoteModel->vote($data[0], Prints::REQUEST_VECTOR,
                            isset($data[2]) ? intval($data[2]) : -1);
                    }
                }
            }

            if(!$live)
                setcookie('myrequests', '', time() - 1000, '/');
        }
    }

    public function getRequestsList($count, $offset = 0)
    {
        $raw = $this->order('`request_date` DESC')->limit($offset, $count)->fetchAll();
        return array_map(function($request) {
            return [
                'title' => $request['full_name'],
                'vector' => $request['vector_id'],
                'url' => Prints::createUrlForItem($request, Prints::REQUEST_VECTOR)
            ];
        }, $raw);
    }

    public function getRequestAuthor($id)
    {
        return $this->select(['user_id', 'request_date'])->where('id', $id)->fetch();
    }

    public function getSearchModel(){
        if(isset($_COOKIE['my_search_model'])) {
            $prm = explode('::', $_COOKIE['my_search_model']);
            return array(
                'category_id' => $prm[0],
                'make_id' => $prm[1],
                'name' => $prm[2],
                'spec' => $prm[3],
                'year' => $prm[4]
            );
        }else{
            return false;
        }
    }

    public function getSearchWhere($prm){
        $where = '`category_id` = ' . $prm['category_id'];
        if(!empty($prm['make_id'])) {
            $where .= ' AND `make_id` = ' . $prm['make_id'];
        }
        if(!empty($prm['name'])){
            $where .= ' AND `full_name` LIKE "%' . $this->escape($prm['name']) . '%"';
        }
        return $where;
    }
}
