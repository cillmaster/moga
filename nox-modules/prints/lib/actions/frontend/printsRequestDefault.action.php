<?php
/**
 * Страница запроса чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsRequestDefaultAction extends noxThemeAction
{
    public $requestVectorModel;
    public $categoryModel;
    public $makeModel;

    public $categories;

    public function execute()
    {
        $this->categoryModel = new printsCategoryModel();
        $this->categories = $this->categoryModel->getActiveAll();
        $this->makeModel = new printsMakeModel();
        $this->requestVectorModel = new printsRequestVectorModel();

        if(isset($_GET['request'])) {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: /requests');
            exit();
        }

        if(isset($_POST['request'])) {
            setcookie('my_search_model', '', time() - 1000, '/');
            $rawRequest = $_POST['request'];

            if(!noxSystem::authorization()) {
                $captcha = md5(md5(@$_POST['captcha']));
                $captcha_right = false;
                if (isset($_COOKIE['PHPImageCode'])) {
                    session_id($_COOKIE['PHPImageCode']);
                    session_start();
                    $captcha_right = @$_SESSION['securityCode'] === $captcha;
                    session_destroy();
                }
            }

            if(isset($captcha_right) && !$captcha_right) {

            } else {
                $fullName = '';
                $sortName = '';
                $categoryId = &$rawRequest['category_id'];

                $nameAr = explode(' ', $rawRequest['name']);
                foreach($nameAr as &$word) {
                    $word = ucfirst($word);
                }
                $rawRequest['name'] = implode(' ', $nameAr);
                unset($nameAr);
                $rawRequest['name'] = htmlspecialchars($rawRequest['name']);

                $detailsAr = explode(' ', $rawRequest['details']);
                foreach($detailsAr as &$word) {
                    $word = ucfirst($word);
                }
                $rawRequest['details'] = implode(' ', $detailsAr);
                unset($detailsAr);
                $rawRequest['details'] = htmlspecialchars($rawRequest['details']);

                $requestVector = [
                    'name' => $rawRequest['name'],
                    'name_details' => $rawRequest['details'],
                    'category_id' => $categoryId,
                ];

                if(isset($rawRequest['year']) && is_numeric($rawRequest['year'])) {
                    $requestVector['year'] = $rawRequest['year'];
                    $fullName .= $rawRequest['year'] . ' ';
                }

                if(isset($rawRequest['make_id'])) {
                    $make = $this->makeModel->getById($rawRequest['make_id']);
                    if($make && ($make['class_id'] == $categoryId)) {
                        $requestVector['make_id'] = $rawRequest['make_id'];
                        $fullName .= $make['name'] . ' ';
                        $sortName .= $make['name'] . ' ';
                    }
                }
                $nameEnd = $rawRequest['name'] . ' ' . $rawRequest['details'];
                $fullName .= $nameEnd;
                $sortName .= $nameEnd;

                $pay = (int)$rawRequest['want_pay'];

                if($equalRequest = $this->requestVectorModel->where($requestVector)->fetch()) {
                    if(noxSystem::authorization()) {
                        if($equalRequest['status'] == 17){
                            setcookie('vote_email', 'cant', time() + 1000, '/');
                        } else {
                            (new printsRequestVoteModel())->vote($equalRequest['id'], Prints::REQUEST_VECTOR, $pay);
                            setcookie('vote_email', $pay ? 'want_pay' : 'want_free', time() + 1000, '/');
                        }
                        noxSystem::location(Prints::createUrlForItem($equalRequest, Prints::REQUEST_VECTOR));
                    } else {
                        noxSystem::location(Prints::createUrlForItem($equalRequest, Prints::REQUEST_VECTOR) . '#popup_registration');
                    }
                }

                if($userId = noxSystem::getUserId()) {
                    $requestVector['user_id'] = $userId;
                }
                $requestVector['full_name'] = $fullName;
                $requestVector['sort_name'] = $sortName;
                $requestVector['url'] = URLTools::string2url($fullName);
                $requestVector['request_date'] = noxDate::toSql();

                if(isset($_COOKIE['nox_utm'])) {
                    $requestVector['utm_value'] = $_COOKIE['nox_utm'];
                }

                $this->requestVectorModel->insert($requestVector);
                $requestVector['id'] = $this->requestVectorModel->insertId();

                if(!noxSystem::authorization()) {
                    $cookie = $requestVector['id']
                        . '::' . printsRequestVectorModel::generateSecretCodeForRequest($requestVector)
                        . '::' . $rawRequest['want_pay'];
                    if(isset($_COOKIE['myrequests'])) {
                        $myRequests = $_COOKIE['myrequests'] . '#.#' . $cookie;
                    } else {
                        $myRequests = $cookie;
                    }
                    setcookie('myrequests', $myRequests, null, '/');
                    setcookie('unauthorized_request', 'true', time() + 1000, '/');
                } else {
                    (new printsRequestVoteModel())->vote($requestVector['id'], Prints::REQUEST_VECTOR, $pay);
                    setcookie('vote_email', $pay ? 'want_pay' : 'want_free', time() + 1000, '/');
                }
                noxSystem::location(Prints::createUrlForItem($requestVector, Prints::REQUEST_VECTOR));
            }
        }

        $prm = array(
            'category_id' => 1,
            'make_id' => ''
        );
        if(isset($_COOKIE['my_search_q'])){
            $nameAr = explode(' ', trim($_COOKIE['my_search_q']));
            foreach($nameAr as &$word) {
                $word = ucfirst($word);
            }
            if($make_id = (new printsMakeModel())
                ->where(['name' => $nameAr[0], 'class_id' => $prm['category_id']])->fetch('id')){
                $prm['make_id'] = $make_id;
                array_shift($nameAr);
            }
            $prm['name'] = implode(' ', $nameAr);
            unset($nameAr);
            setcookie('my_search_q', '', time() - 1000, '/');
        }else if($prm_model = $this->requestVectorModel->getSearchModel()){
            $prm = $prm_model;
        }
        $this->addVar('prm', $prm);
        $this->requestVectorModel->where($this->requestVectorModel->getSearchWhere($prm));

        $categories = new noxTemplate($this->moduleFolder . '/templates/frontend/categoryOptions.html');
        $categories->addVar('selected', $prm['category_id']);
        $categories->addVar('res', $this->categories);
        $this->addVar('categories', $categories->__toString());

        $res = $this->makeModel->where('class_id', $prm['category_id'])->select('id, name')->order('name')->fetchAll('id');

        $makesSelectOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/makeOptions.html');
        $makesSelectOptions->addVar('selected', $prm['make_id']);
        $makesSelectOptions->addVar('res', $res);
        $this->addVar('makesSelectOptions', $makesSelectOptions->__toString());

        $makesDataOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/optionsDatalistMake.html');
        $makesDataOptions->addVar('res', $res);
        $this->addVar('makesDataOptions', $makesDataOptions->__toString());

        if(!empty($prm['make_id'])){
            $tmp = new noxDbQuery();
            $tmp->exec('SELECT `db_table` FROM `prints_class` WHERE `id` = ' . $prm['category_id']);
            $pref = $tmp->fetch('db_table');
            $tmp = new noxDbQuery();
            $tmp->exec('SELECT \'\' as id, `name` FROM `prints_vector` 
                LEFT JOIN `prints_class_' . $pref .'` ON `prints_vector`.`item_id` = `prints_class_' . $pref .'`.`id` 
                WHERE `make_id` = ' . $prm['make_id'] . ' GROUP BY `name`');
            $namesDataOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/optionsDatalistMake.html');
            $namesDataOptions->addVar('res', $tmp->fetchAll());

            $this->addVar('namesDataOptions', $namesDataOptions->__toString());
        }else{
            $this->addVar('namesDataOptions', '');
        }

        $requestsSearch = new noxTemplate($this->moduleFolder . '/templates/frontend/requestSearch.html');
        $page = $_GET['page'];
        $config = noxConfig::getConfig();
        $url = $config['protocol'] . $config['host'] . '/requests';
        $requestsSearch->addVar('pager', (new kafPager('pager2.html'))->create2($count = $this->requestVectorModel->count(), $onPage = 50, 3, $page));
        if($page > 1){
            $this->addVar('prevPage', $url . '?page=' . ($page - 1));
        }
        if($page * $onPage < $count){
            $this->addVar('nextPage', $url . '?page=' . ($page + 1));
        }
        if(isset($page) && $page > 1 && isset($count) && isset($onPage)){
            $this->title = 'Request and order any blueprints - Page ' . $page;
            $metaDescription = '';
        } else {
            $this->title = 'Request and order any blueprints';
            $metaDescription = 'Request drawings or blueprints and get it in a few days!';
        }
        $this->caption = 'Request and order any blueprints';
        $this->addMetaDescription($metaDescription);
        $res = $this->requestVectorModel->getRequestsList($onPage, ($page - 1) * $onPage);
        $requestsSearch->addVar('res', $res);
        $this->addVar('requestsSearch', $requestsSearch->__toString());

        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'chrome') === false){
            $this->addVar('brouser', 'brouser-not-like-chrome');
        }else{
            $this->addVar('brouser', 'brouser-like-chrome');
        }

        $this->addVar('requestsPage', true);
    }
}
