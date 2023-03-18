<?php

class printsRequestActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsRequestVectorModel
     */
    public $requestVectorModel;
    /**
     * @var printsRequestVoteModel
     */
    public $voteModel;
    public $categoryModel;

    public $categories;

    public function execute()
    {
        $this->requestVectorModel = new printsRequestVectorModel();
        $this->voteModel = new printsRequestVoteModel();
        return parent::execute();
    }

    public function actionDefault() {
        if (!$this->haveRight('request')) {
            return 401;
        }
        $orders = ['votes DESC', 'request_date DESC', 'sort_name'];

        if($_GET['status'] > 0 && $_GET['id'] > 0) {
            $this->requestVectorModel->updateById($_GET['id'], [
                'status' => $_GET['status'],
                'update_date' => noxDate::toSql(),
            ]);
            exit;
        }
        $this->categoryModel = new printsCategoryModel();
        $this->categories = $this->categoryModel->fetchAll('id');
        $this->addVar('categories', $this->categories);
        $where = '';

        if($_GET['filter']['category_id'] && isset($this->categories[$_GET['filter']['category_id']])) {
            $makeModel = new printsMakeModel();
            $this->addVar('makes', $makeModel->getAllByCategory($_GET['filter']['category_id']));
            if($_GET['filter']['make_id'] == 0) {
                unset($_GET['filter']['make_id']);
            }
            foreach ($_GET['filter'] as $k=>$v) {
                if($where) $where .= ' AND ';
                $where .= '`' . $k . '` = "' . $v . '"';
            }
        }

        if($_GET['status'] && is_numeric($_GET['status'])) {
            if($where) $where .= ' AND ';
            $where .= '`status` = "' . $_GET['status'] . '"';
        }

        if($_GET['author'] && is_numeric($_GET['author'])) {
            if($where) $where .= ' AND ';

            if($_GET['contact'] && is_numeric($_GET['contact'])) {
                if($_GET['contact'] == '2') {
                    $where .= '`user_id` IN(SELECT `id` FROM `nox_user` WHERE `user_type` = "facebook")';
                }
                else {
                    $where .= '`user_id` IN(SELECT `id` FROM `nox_user` WHERE `user_type` IN("email", "google"))';
                }
            }
            else {
                if($_GET['author'] == '2') {
                    $where .= '`user_id` IS NULL';
                }
                else {
                    $where .= '`user_id` IS NOT NULL';
                }
            }
        }

        if($_GET['search'] != '') {
            if($where) $where .= ' AND ';
            $where .= '`full_name` LIKE "%' . $this->requestVectorModel->escape($_GET['search']) . '%"';
        }

        $orderId = (empty($_GET['order']) || empty($orders[$_GET['order']]) )? 0 : $_GET['order'];
        $this->requestVectorModel->order($orders[$orderId]);

        $this->requestVectorModel->select('* , (SELECT COUNT( * ) FROM prints_request_vote WHERE request_type =9 AND request_id = prints_request_vector.id) AS  "votes"');
        $where && $this->requestVectorModel->where($where);

        $page = $_GET['page'];
        $this->addVar('pager', (new kafPager())->create($this->requestVectorModel->count(), $onPage = 100, 5));

        $res = $this->requestVectorModel->limit(($page-1)*$onPage, $onPage)->fetchAll('id');

        array_map(function($request) {
            if($request['description'] !== NULL) {
                $request['description'] = json_decode($request['description'], true);
            }
        }, $res);

        $this->title = 'Запросы векторов';
        $this->addVar('res', $res);

        $usersId = [];
        foreach($res as $ar) {
            if($ar['user_id']) {
                $usersId[$ar['user_id']] = $ar['user_id'];
            }
        }

        if(!empty($usersId)) {
            $userModel = noxSystem::getUserModel()->reset();
            $users = $userModel->where('id', $usersId)->fetchAll('id');
        }
        else {
            $users = false;
        }
        $this->addVar('users', $users);
    }

    public function actionEdit()
    {
        if (!$this->haveRight('request')) {
            return 401;
        }
        $id = @$_GET['id'];
        if(!$id) {
            return 401;
        }

        $request = $this->requestVectorModel->getById($id);
        $f = new noxModelForm($this->requestVectorModel);
        if($request['year'] == '0000') $request['year'] = '';

        $inputName = $f->getFormInputName();
        if(isset($_POST[$inputName])) {
            $fullName = '';
            $requestVector = $_POST[$inputName];

            if($requestVector['year'] && !is_numeric($requestVector['year'])) {
                $requestVector['year'] = '';
            }
            if($requestVector['year']) {
                $fullName .= $requestVector['year'] . ' ';
            }

            if($requestVector['make_id']) {
                $make =(new printsMakeModel())->getById($requestVector['make_id']);
                $fullName .= $make['name'] . ' ';
            }
            else {
                $requestVector['make_id'] = NULL;
            }

            if($request['category_id'] != $requestVector['category_id']) {
                $requestVector['make_id'] = NULL;
            }
            $fullName .= $requestVector['name'];
            $requestVector['full_name'] = $fullName;
            $requestVector['url'] = URLTools::string2url($fullName);
            $requestVector['update_date'] = noxDate::toSql();

            $this->requestVectorModel->updateById($id, $requestVector);
            noxSystem::location('?section=request');
        }

        $this->title = 'Редактирование запроса вектора';
        $f->acceptedFields('name,category_id,make_id,year');

        $f->model->fields['make_id']['sql_where'] = 'class_id = ' . $request['category_id'];
        $f->setValues($request);

        echo $f;
    }

    public function actionDelete()
    {
        if (!$this->haveRight('request')) {
            return 401;
        }
        $id = getParam($_GET['id']);
        if (!$id) {
            return 400;
        }

        $this->requestVectorModel->deleteById($id);

        //TODO: Пишем в лог
        if ($this->ajax()) {
            return 200;
        } else {
            noxSystem::location('?section=request');
        }
    }

    public function actionLink()
    {
        if (!$this->haveRight('request')) {
            return 401;
        }
        $requestId = @$_GET['id'];
        if(!$requestId) {
            return 401;
        }

        $list = explode('|', $requestId);
        if(isset($_REQUEST['vector_id'])){
            foreach ($list as $item){
                $this->requestVectorModel->updateById($item, [
                        'vector_id' => $_REQUEST['vector_id'],
                        'status' => 12,
                        'update_date' => noxDate::toSql(),
                    ]
                );
            }
        }

        if($this->ajax()) {
            echo 'ok';
            return 200;
        }else{
            $ar = $this->requestVectorModel->getById($list[0]);
            $this->addVar('ar', $ar);

            if($ar['vector_id']) {
                $this->addVar('vector', (new printsVectorModel())->getById($ar['vector_id']));
            }
        }

    }

    public function actionCrm() {
        if (!$this->haveRight('requestCRM')) {
            return 401;
        }
        if($this->haveRight('control')){
            $admin = true;
            $this->addVar('admin', $admin);
            $update_count_max = 6;
        }else{
            $_GET['want_pay'] = 3;
            $update_count_max = 1;
        }
        $id = (int)$_GET['id'];
        $status = (int)$_GET['status'];
        if($status > 0 && $id > 0) {
            if(in_array($status, [18, 19]) || $this->haveRight('control')){
                $where['status'] = $_GET['status'];
                if(in_array($status, [9, 10, 12])) {
                    $vote = $this->voteModel->getById($id);
                    $inc = 1;
                    switch((int)$vote['update_count']) {
                        case 0:
                            $newDate = strtotime('+2 days');
                            break;
                        case 1:
                            $newDate = strtotime('+3 days');
                            break;
                        case 2:
                            $newDate = strtotime('+7 days');
                            break;
                        case 3:
                            $newDate = strtotime('+30 days');
                            break;
                        case 4:
                            $newDate = NULL;
                            break;
                        default:
                            $inc = 0;
                            $newDate = NULL;
                            break;
                    }
                    $where['update_count'] = $vote['update_count'] + $inc;
                }else{
                    $newDate = NULL;
                }

                $where['update_datetime'] = ($newDate != NULL) ? noxDate::toSql($newDate) : $newDate;

                $this->voteModel->updateById($id, $where);
                exit;
            } else {
                return 401;
            }
        }
        if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
            $this->voteModel->deleteById($_GET['delete']);
            if($this->ajax()) exit;
        }

//        $this->voteModel->updateByField(['update_datetime' => [
//            'begin' => noxDate::toSql(1),
//            'end' => noxDate::toSql(time())
//            ]], false, ['status' => 1, 'update_datetime' => NULL]);
        $this->voteModel->reset();


        $this->categoryModel = new printsCategoryModel();
        $this->categories = $this->categoryModel->fetchAll('id');
        $this->addVar('categories', $this->categories);
        $where = '';

        if($_GET['filter']['category_id'] > 0 && isset($this->categories[$_GET['filter']['category_id']])) {
            $makeModel = new printsMakeModel();
            $this->addVar('makes', $makeModel->getAllByCategory($_GET['filter']['category_id']));
            if(!$_GET['filter']['make_id']) {
                unset($_GET['filter']['make_id']);
            }
            unset($_GET['filter']['set_id']);
            if($where) $where .= ' AND ';
            $where .= 'request_id IN(select id from prints_request_vector WHERE ';
            $l = 0;
            foreach ($_GET['filter'] as $k=>$v) {
                if($l) $where .= ' AND ';
                $where .= '`' . $k . '` = "' . $v . '"';
                $l++;
            }
            if(!isset($_GET['filter']['make_id'])) {
                $_GET['filter']['make_id'] = 0;
            }
            $where .= ')';
        }
        if($_GET['search'] != '') {
            $_GET['days'] = 0;
            if($where) $where .= ' AND ';
            $where .= 'request_id IN(select id from prints_request_vector WHERE `full_name` LIKE "%' . $this->requestVectorModel->escape($_GET['search']) . '%")';
        }

        if($_GET['status'] > 0) {
            if($where) $where .= ' AND ';
            $where .= '`status` = ' . $_GET['status'];
        }

        if($_GET['update_count']) {
            if($where) $where .= ' AND ';
            $where .= '`update_count` = ' . ($_GET['update_count'] - 1);
        }else if(!isset($admin)){
            if($where) $where .= ' AND ';
            $where .= '`update_count` < 2';
        }
        for($i = 0; $i <= $update_count_max; $i++)
            $update_count[$i + 1] = $i;
        $this->addVar('update_count', $update_count);

        if($_GET['want_pay']) {
            if($where) $where .= ' AND ';
            $where .= '`want_pay` = ' . ($_GET['want_pay'] - 2);
        }
        $want_pay = array(1 => 'Empty', 'Free', 'Prepay');
        $this->addVar('want_pay', $want_pay);

        if($_GET['days']) {
            if($where) $where .= ' AND ';
            $where .= '`vote_datetime` >= "' . noxDate::toSql(time() - $_GET['days'] * 86400) . '"';
        }
        $this->addVar('days', array(
            90 => 'три месяца'
        ));

        if($_GET['contact'] && is_numeric($_GET['contact'])) {
            if($where) $where .= ' AND ';
            if($_GET['contact'] == '2') {
                $where .= '`user_id` IN(SELECT `id` FROM `nox_user` WHERE `user_type` = "facebook")';
            }
            else {
                $where .= '`user_id` IN(SELECT `id` FROM `nox_user` WHERE `user_type` IN("email", "google"))';
            }
        }

        $this->voteModel
            ->select('* , request_id AS RID, (SELECT COUNT( * ) FROM  `prints_request_vote`
            WHERE request_type = 9 AND request_id = RID) AS votes');
        $where && $this->voteModel->where($where);

        $page = $_GET['page'];
        $this->addVar('pager', (new kafPager())->create($this->voteModel->count(), $onPage = 100, 5));

        $res = $this->voteModel->limit(($page-1)*$onPage, $onPage)->order('vote_datetime DESC')->fetchAll('id');
        $this->title = 'CRM запросы';

        $this->addVar('is_filter', $_GET['search'] != '' ||  $_GET['filter']['category_id'] > 0
            || $_GET['contact'] > 0);

        $usersId = [];
        $requestsId = [];
        foreach($res as $ar) {
            if($ar['user_id']) {
                $usersId[$ar['user_id']] = $ar['user_id'];
                $requestsId[$ar['request_id']] = $ar['request_id'];
            }
        }

        if(!empty($requestsId)) {
            $r = $this->requestVectorModel->where('id', $requestsId)->fetchAll('id', 'full_name');
        }
        else {
            $r = [];
        }

        if(!empty($usersId)) {
            $userModel = noxSystem::getUserModel()->reset();
            $users = $userModel->where('id', $usersId)->fetchAll('id');
            $authors = $this->requestVectorModel->reset()->select('DISTINCT user_id')
                ->where('user_id', $usersId)->fetchAll(null, 'user_id');
            $buyers = (new paymentModel())->select('DISTINCT user_id')->where([
                    'user_id' => $usersId,
                    'status' => 'approved'
                ])->fetchAll(null, 'user_id');
            $business = (new noxUserGroupsModel())->select('DISTINCT user_id')->where([
                    'user_id' => $usersId,
                    'group_id' => Users::businessGroupId
                ])->fetchAll(null, 'user_id');
        }
        else {
            $users = [];
            $authors = [];
            $buyers = [];
            $business = [];
        }
        foreach ($res as &$item){
            $rid = $item['request_id'];
            $item['name'] = isset($r[$rid]) ? $r[$rid] : '';
            $uid = $item['user_id'];
            $item['user'] = isset($users[$uid]) ? $users[$uid] : [];
            $item['class'] = (in_array($uid, $business) || in_array($uid, $buyers))
                ? 'red' : (in_array($uid, $authors) ? 'orange' : '');
        }
        $this->addVar('res', $res);
    }
}
