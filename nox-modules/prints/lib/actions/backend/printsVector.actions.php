<?php
/**
 * Администрирование векторов
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsVectorActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsVectorModel
     */
    public $model;
    /**
     * @var printsCategoryModel
     */
    public $categoryModel;
    /**
     * @var printsMakeModel
     */
    public $makeModel;
    /**
     * @var printsSetModel
     */
    public $setModel;

    /**
     * @var array
     */
    public $categories;
    public $makes;
    public $collectionModel;

    public $editorID;

    public $caption = 'Векторы';

    public function execute()
    {
        if (!$this->haveRight('vector')) {
            return 401;
        }
        $this->editorID = noxSystem::getUserId();
        $this->model = new printsVectorModel();
        $this->categoryModel = new printsCategoryModel();
        $this->makeModel = new printsMakeModel();
        $this->setModel = new printsSetModel();
        $this->collectionModel = new printsCollectionModel();

        $this->categories = $this->categoryModel->getActiveAll();

        $this->addVar('categories', $this->categories);
        return parent::execute();
    }

    public function actionDefault() {
        if(isset($_POST['collection'])){
            if(isset($_POST['check'])){
                $cID = $_POST['collection'];
                foreach ($_POST['check'] as $key => $value){
                    $this->collectionModel->addVector($cID, $key);
                }
            }
            $_GET['collection'] = $_POST['collection'];
        }
        $this->addVar('collections', $this->collectionModel->reset()->select(['id', 'name'])->order('name')->fetchAll());
        $order_force = isset($_GET['order_force']) ? intval($_GET['order_force']) : 0;
        $_GET['order'] = $order_force ? $_GET['order'] : 0;
        $mapSearch = ['name', 'id'];
        if(!isset($_GET['search_type'])){
            $_GET['search_type'] = 0;
        }
        $searchType = $mapSearch[$_GET['search_type']];
        $this->addVar('type', $searchType);
        $where = '';
        if(($searchType == 'id') && (int)$_GET['search']){
            $where = '`id` = ' . (int)$_GET['search'];
        } else {
            if($_GET['filter']['category_id'] == 0)
                $_GET['filter']['category_id'] = 1;
            if(isset($this->categories[$_GET['filter']['category_id']])) {
                $category = &$this->categories[$_GET['filter']['category_id']];
                $categoryDataTable = 'prints_class_' . $category['db_table'];
                $categoryDataModel = new noxModel(false, $categoryDataTable);

                $where = 'class_id = ' .  $category['id'];
                if($_GET['filter']['make_id'] > 0 && isset($categoryDataModel->fields['make_id']) && is_numeric($_GET['filter']['make_id'])) {
                    $where .= ' AND item_id IN(select id from `' . $categoryDataTable . '` where make_id = ' . $_GET['filter']['make_id'] . ')';
                    if($_GET['filter']['set_id'] > 0 && is_numeric($_GET['filter']['set_id'])) {
                        $where .= ' AND id IN(select vector_id from `prints_set_vector` where `set_id` = ' . $_GET['filter']['set_id'] . ')';
                        $_GET['order'] = $order_force ? $_GET['order'] : 1;
                    }
                    $this->addVar('sets', $this->setModel->getAllByMake($_GET['filter']['make_id']));
                }
                $this->addVar('makes', $this->makeModel->getAllByCategory($category['id']));
            }
            if($_GET['search'] != '') {
                if(!empty($where))
                    $where .= ' AND ';
                $where .= '`full_name` LIKE "%' . $this->model->escape($_GET['search']) . '%"';
            }
            if($_GET['status'] != 0) { // 1 - ready, 2 - prepay
                if(!empty($where))
                    $where .= ' AND ';
                $where .= '`prepay` = ' . (intval($_GET['status']) - 1);
            }
        }
        if(!empty($where))
            $this->model->where($where);

        $page = $_GET['page'];
        $this->addVar('pager', (new kafPager())->create($this->model->count(), $onPage = 100, 5));

        $mapOrder = ['sort_name', 'full_name', 'added_date DESC'];
        $res = $this->model->limit(($page-1)*$onPage, $onPage)->order($mapOrder[$_GET['order']])->fetchAll('id');

        $tmp = new noxDbQuery();
        $tmp->exec('SELECT `vector_id`, `name_full`, `url` FROM `prints_set_vector` LEFT JOIN `prints_set` ON `prints_set`.`id` = `prints_set_vector`.`set_id`');
        $sets = $tmp->fetchAll('vector_id');
        foreach ($res as $row){
            if(noxConfig::isProduction()){
                if(!file_exists(noxRealPath($row['preview']))){
                    $res[$row['id']]['full_name'] .= '<span class="exist_error">preview</span>';
                };
                if(!file_exists(noxRealPath($row['filename']))){
                    $res[$row['id']]['full_name'] .= '<span class="exist_error">arhiv</span>';
                };
            }
            if(isset($sets[$row['id']])){
                $res[$row['id']]['set']['url'] = $sets[$row['id']]['url'];
                $res[$row['id']]['set']['name'] = $sets[$row['id']]['name_full'];
            }
        }

        $user_id = noxSystem::$userControl->user['id'];
        $admin = $this->haveRight('control');
        foreach ($res as &$row){
            $row['can_edit'] = $admin || ($row['add_user_id'] == $user_id);
            $row['can_del'] = $admin || ($row['can_edit'] && (time() - strtotime($row['added_date'])) < 86400 );
            $row['can_download'] = $admin;
        }
        $this->addVar('admin',$admin);
        $this->addVar('res', $res);
        //$this->addVar('editors', in_array(noxSystem::$userControl->user['id'], array(3,26270,27560)));
    }

    /**
     * Добавление
     */
    public function actionAdd()
    {

        $exts = explode(',', str_replace('\'', '', $this->model->fields['ext']['length']));
        $this->addVar('exts', $exts);

        if($_GET['id'] > 0) {
            $ar = $this->model->getById($_GET['id']);
            //$ar['preview'] = noxSystem::$media -> src($ar['preview']);
            $this->addVar('ar', $ar);
            $this->addVar('is_copy');
        }

        if(isset($_GET['request_id'])) {
            $requestVectorModel = new printsRequestVectorModel();
            $request = $requestVectorModel->getById($_GET['request_id']);
            if($request) {
                $this->addVar('request', $request);
                $categoryId = $request['category_id'];
                $make_id = $request['make_id'];
                $year = $request['year'];

                $requestVoteModel = new printsRequestVoteModel();
                $requestVectorModel = new printsRequestVectorModel();
                $list = ($usersVote = $requestVoteModel->getVoteAuthors($_GET['request_id'])) ? $usersVote : [];
                $author = $requestVectorModel->getRequestAuthor($_GET['request_id']);
                if($author){
                    $list[$author['user_id']] = $author['request_date'];
                }
                if($list){
                    $users_model = noxSystem::getUserModel();
                    $users = $users_model->select('id, email, name, user_type')->where('id', array_keys($list))->fetchAll();
                    if(sizeof($users) > 0){
                        foreach ($users as &$user) {
                            $user['real_email'] = ($user['user_type'] != 'facebook')
                                || (($email_ar = explode('@', $user['email'])) && $email_ar[1] != 'facebook.com');
                            $user['dt'] = $list[$user['id']];
                            $user['tmp'] = $this->dateRange($list[$user['id']], [
                                ['t' => '', 'fr' => 'noreply', 'tag' => ''],
                                ['t' => '_sorry', 'fr' => 'crm1', 'tag' => '-sorry1'],
                                ['t' => '_sorry', 'fr' => 'crm2', 'tag' => '-sorry2']
                            ]);
                        }
                        $this->addVar('users', $users);
                    }
                }
            }
        }

        if(($_GET['category_id'] > 0 && isset($this->categories[$_GET['category_id']])) || (isset($ar) && $ar)) {
            $categoryId = ($_GET['category_id'] > 0 ? $_GET['category_id'] : $ar['class_id']);
        }
        //Сохраняем
        if ((isset($_POST['submit']) || isset($_POST['submit_with_view'])) && isset($categoryId)) {
            $new = $_POST['new'];

            if(isset($new['email'])){
                $send_email = true;
                unset($new['email']);
            }

            $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$categoryId]['db_table']);
            $form = new noxModelForm($categoryModel);

            $categoryData = $_POST[$form->getFormInputName()];
            unset($categoryData['id']);
            if($categoryModel->insert($categoryData)) {
                $new['class_id'] = $categoryId;
                $new['item_id'] = $categoryModel->insertId();
                //Обновление информации о файле
                $new['update_date'] = noxDate::toSql();
                $new['updated_by'] = $this->editorID;
                $new['views'] = isset($new['views']) ? Views::array2int($new['views']) : 0;
                $new['ext'] = (isset($new['ext'])) ? implode(',', $new['ext']) : '';
                $new['prepay'] = isset($new['prepay']) ? intval($new['prepay']) : 0;
                if(!$new['prepay']){
                    $operImages = new systemAdministratorImagesAction();
                    $operImages->check208($new['preview']);
                }
                $new['full_name'] = Prints::generateFullName($new, $categoryData);
                $new['prm'] = Prints::generatePrm($categoryData);
                $new['sort_name'] = Prints::generateSortName($new, $categoryData);
                $new['url'] = Prints::generateSEOUrl($new, $categoryData, Prints::VECTOR);
                $new['add_user_id'] = noxSystem::$userControl -> user['id'];

                $this->model->insert($new);
                $vectorId = $this->model->insertId();

                if(isset($_GET['id']) && $_GET['id']) {
                    $tagVectorModel = new tagVectorModel();
                    $relatedTags = $tagVectorModel->where($tagVectorModel->categoryName, $_GET['id'])->fetchAll();
                    if(!empty($relatedTags)) {
                        array_walk(
                            $relatedTags,
                            function(&$ar) use ($tagVectorModel, $vectorId){
                                $ar[$tagVectorModel->categoryName] = $vectorId;
                            }
                        );
                        foreach ($relatedTags as $item)
                            $tagVectorModel->insert($item);
                    }
                }

                //TODO: Пишем в лог
                //Пересчитываем количество чертежей марки
                if(isset($categoryData['make_id'])) {
                    $itemsId = $categoryModel->select('id')->where('make_id', $categoryData['make_id'])->fetchAll(false, 'id');
                    if(!$itemsId) {
                        $count = 0;
                    }
                    else {
                        $count = $this->model->reset()->where(['class_id' => $categoryId, 'item_id' => $itemsId])->count();
                    }
                    $this->makeModel->updateById($categoryData['make_id'], array('vectors_count' => $count));
                }

                if(isset($request) && $vectorId) {
                    $requestVectorModel->updateById($request['id'], [
                            'vector_id' => $vectorId,
                            'status' => 12
                        ]
                    );
                    if(isset($send_email) && isset($users) && sizeof($users) > 0){
                        $logEvents = new logEventsModel();
                        $email_car = $request['full_name'];
                        $email_subject = 'Get ' . $email_car . ' vector blueprints - Finish your prepay order';
                        $email_link = 'http://' . noxSystem::$domain . Prints::createUrlForItem([
                                'id' => $vectorId,
                                'url' => $new['url']
                            ], Prints::VECTOR);
                        $email_title = $new['full_name'] . ' blueprints';
                        foreach ($users as &$user){
                            if($user['real_email']){
                                $prm = array(
                                    'subject' => $email_subject,
                                    'from' => $user['tmp']['fr'],
                                    'to' => $user['email'],
                                    'date' => date('j M Y', strtotime($user['dt'])),
                                    'car' => $email_car,
                                    'link' => $email_link,
                                    'title' => $email_title,
                                    'UserID' => $user['id'],
                                    'tag' => 'prepay-link' . $user['tmp']['tag']
                                );
                                if(!empty($user['name'])){
                                    $prm['name'] = $user['name'];
                                    $prm['subject'] .= ' - ' . $user['name'];
                                }
                                (new postmarkMailer('prepay_link' . $user['tmp']['t']))->mail($prm);
                                $prm['email_template'] = 'prepay_link' . $user['tmp']['t'];
                                $tm = time();
                                $logPrm = [
                                    'type' => 1,
                                    'prm' => json_encode($prm),
                                    'user_id' => $prm['UserID'],
                                    'date_create' => $tm
                                ];
                                if(strtotime($user['dt']) > 1555286400){ // 15.04.2019
                                    $userTime = strtotime($user['dt']);
                                    $userTime = $userTime - floor($userTime / 86400) * 86400;
                                    $logPrm['date_check'] = floor($tm / 86400 + 5) * 86400 + $userTime;
                                }
                                $logEvents->insert($logPrm);
                                (new printsRequestVoteModel())->updateByField([
                                    'request_id' => $_GET['request_id'],
                                    'user_id' => $user['id']
                                ], false, [
                                    'status' => 12,
                                    'update_datetime' => NULL
                                ]);
                            }
                        }
                    }
                }

                $set_id = (new printsSetModel())->updateSets($new);

                noxSystem::location('/administrator/prints/?section=vector&filter[category_id]=' . $categoryId
                    . '&filter[make_id]=' . $categoryData['make_id']
                    . ($set_id > 0 ? ('&filter[set_id]=' . $set_id) : '')
                    . (isset($_POST['submit_with_view']) ? ('view=' . $vectorId) : '')
                );

            }
            else {
                throw new noxException('Ошибка при добавлении чертежа!');
            }
        }

        $this->caption = 'Добавление вектора';

        if($categoryId > 0) {
            $this->addVar('step', 2);
            $this->addVar('category', $this->categories[$categoryId]);

            $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$categoryId]['db_table']);
            $form = new noxModelForm($categoryModel);
            $form->onlyFields = true;
            $form->setValues(['make_id' => isset($make_id) ? $make_id : @$_GET['make_id']]);
            if(isset($form->model->fields['make_id'])) {
                $form->model->fields['make_id']['sql_where'] = 'class_id = ' . $categoryId;
                $form->addFieldsParams(['make_id' => ['class' => 'js-change-data-folder']]);
                if(isset($year))
                    $form->setValues(['year' => $year]);
            }

            if(isset($ar)) {
                $values = $categoryModel->getById($ar['item_id']);
                unset($values['id']);
                $form->setValues($values);
            }

            $this->addVar('f', $form);
            $this->addVar('makeUrls', $this->makeModel->reset()->where('class_id', $categoryId)->fetchAll('id', 'url'));
        }
        else {
            $this->addVar('step', 1);
            $this->addVar('cats', $this->categoryModel->getActiveList());
        }
        $admin = $this->haveRight('control');
        $this->addVar('admin', $admin);
        $this->addVar('prepay_default', in_array(noxSystem::$userControl->user['id'], array(/*3,*/ 26270)));
    }

    private function dateRange($date, $map){
        $diff = (time() - strtotime($date)) / 86400;
        return $map[$diff > 270 ? 2 : ($diff > 90 ? 1 : 0)];
    }

    /**
     * Привязка вектора к запросу CRM с рассылкой уведомлений
     */
    public function actionLink()
    {
        if(isset($_GET['request_id'])) {
            $requestVectorModel = new printsRequestVectorModel();
            $request = $requestVectorModel->getById($_GET['request_id']);
            if($request) {
                $this->addVar('request', $request);

                $requestVoteModel = new printsRequestVoteModel();
                $requestVectorModel = new printsRequestVectorModel();
                $list = ($usersVote = $requestVoteModel->getVoteAuthors($_GET['request_id'])) ? $usersVote : [];
                $author = $requestVectorModel->getRequestAuthor($_GET['request_id']);
                if($author){
                    $list[$author['user_id']] = $author['request_date'];
                }
                if($list){
                    $users_model = noxSystem::getUserModel();
                    $users = $users_model->select('id, email, name, user_type')->where('id', array_keys($list))->fetchAll();
                    if(sizeof($users) > 0){
                        foreach ($users as &$user) {
                            $user['real_email'] = ($user['user_type'] != 'facebook')
                                || (($email_ar = explode('@', $user['email'])) && $email_ar[1] != 'facebook.com');
                            $user['dt'] = $list[$user['id']];
                            $user['tmp'] = $this->dateRange($list[$user['id']], [
                                ['t' => '', 'fr' => 'noreply', 'tag' => ''],
                                ['t' => '_sorry', 'fr' => 'crm1', 'tag' => '-sorry1'],
                                ['t' => '_sorry', 'fr' => 'crm2', 'tag' => '-sorry2']
                            ]);
                        }
                        $this->addVar('users', $users);
                    }
                }

                //Сохраняем
                if (isset($_POST['submit']) && isset($_POST['vector_id'])) {
                    $vector = $this->model->getById($_POST['vector_id']);
                    if ($vector) {
                        $vectorId = $vector['id'];
                        $requestVectorModel->updateById($request['id'], [
                                'vector_id' => $vectorId,
                                'status' => 12
                            ]
                        );
                        if(isset($_POST['email']) && isset($users) && sizeof($users) > 0){
                            $logEvents = new logEventsModel();
                            $email_car = $request['full_name'];
                            if($vector['prepay']){
                                $email_subject = 'Get ' . $email_car . ' vector blueprints - Finish your prepay order';
                                $email_template = 'prepay_link';
                                $tag = 'prepay-link';
                            }else{
                                $prm = json_decode($vector['prm']);
                                $email_subject = $prm->year . ' ' . $prm->make . ' ' . $vector['name'] .
                                    ' vector blueprint is ready - Download it now';
                                $email_template = 'vector_link';
                                $tag = 'vector-link';
                            }
                            $email_link = 'http://' . noxSystem::$domain . Prints::createUrlForItem([
                                    'id' => $vectorId,
                                    'url' => $vector['url']
                                ], Prints::VECTOR);
                            $email_title = $vector['full_name'] . ' blueprints';
                            foreach ($users as &$user){
                                if($user['real_email']){
                                    $prm = array(
                                        'subject' => $email_subject,
                                        'from' => $user['tmp']['fr'],
                                        'to' => $user['email'],
                                        'date' => date('j M Y', strtotime($user['dt'])),
                                        'car' => $email_car,
                                        'link' => $email_link,
                                        'title' => $email_title,
                                        'UserID' => $user['id'],
                                        'tag' => $tag . $user['tmp']['tag']
                                    );
                                    if(!empty($user['name'])){
                                        $prm['name'] = $user['name'];
                                        $prm['subject'] .= ' - ' . $user['name'];
                                    }
                                    (new postmarkMailer($email_template . $user['tmp']['t']))->mail($prm);
                                    $prm['email_template'] = $email_template . $user['tmp']['t'];
                                    $tm = time();
                                    $logPrm = [
                                        'type' => 2,
                                        'prm' => json_encode($prm),
                                        'user_id' => $prm['UserID'],
                                        'date_create' => $tm
                                    ];
                                    if(strtotime($user['dt']) > 1555286400){ // 15.04.2019
                                        $userTime = strtotime($user['dt']);
                                        $userTime = $userTime - floor($userTime / 86400) * 86400;
                                        $logPrm['date_check'] = floor($tm / 86400 + 5) * 86400 + $userTime;
                                    }
                                    $logEvents->insert($logPrm);
                                }
                                $requestVoteModel->updateByField([
                                    'request_id' => $_GET['request_id'],
                                    'user_id' => $user['id']
                                ], false, [
                                    'status' => $user['real_email'] ? 12 : 16,
                                    'update_datetime' => NULL
                                ]);
                            }
                        } else {
                            $requestVoteModel->updateByField('request_id', $_GET['request_id'],[
                                'status' => 16,
                                'update_datetime' => NULL
                            ]);
                        }
                        noxSystem::location('/administrator/prints/?section=request&action=crm&status=1&update_count=1&days=90&want_pay=3');
                    }
                }
                $this->caption = 'Привязка вектора';
                $admin = $this->haveRight('control');
                $this->addVar('admin', $admin);
            }else{
                noxSystem::location('/administrator');
            }
        }else{
            noxSystem::location('/administrator');
        }
    }

    /**
     * Отбраковка запроса CRM с рассылкой уведомлений
     */
    public function actionCant()
    {
        if(isset($_GET['request_id'])) {
            $rid = $_GET['request_id'];
            $requestVectorModel = new printsRequestVectorModel();
            $request = $requestVectorModel->getById($rid);
            if($request) {
                $this->addVar('request', $request);

                $requestVoteModel = new printsRequestVoteModel();
                $requestVectorModel = new printsRequestVectorModel();
                $list = ($usersVote = $requestVoteModel->getVoteAuthors($_GET['request_id'])) ? $usersVote : [];
                $author = $requestVectorModel->getRequestAuthor($_GET['request_id']);
                if($author){
                    $list[$author['user_id']] = $author['request_date'];
                }
                if($list){
                    $users_model = noxSystem::getUserModel();
                    $users = $users_model->select('id, email, name, user_type')->where('id', array_keys($list))->fetchAll();
                    if(sizeof($users) > 0){
                        foreach ($users as &$user) {
                            $user['real_email'] = ($user['user_type'] != 'facebook')
                                || (($email_ar = explode('@', $user['email'])) && $email_ar[1] != 'facebook.com');
                            $user['dt'] = $list[$user['id']];
                            $user['tmp'] = $this->dateRange($list[$user['id']], [
                                ['t' => '', 'fr' => 'noreply', 'tag' => ''],
                                ['t' => '_sorry', 'fr' => 'crm1', 'tag' => '-sorry1'],
                                ['t' => '_sorry', 'fr' => 'crm2', 'tag' => '-sorry2']
                            ]);
                        }
                        $this->addVar('users', $users);
                    }
                }

                //Сохраняем
                if (isset($_POST['submit'])) {
                    $requestVectorModel->updateById($rid,[
                        'status' => 17
                    ]);
                    if(isset($_POST['email']) && isset($users) && sizeof($users) > 0){
                        foreach ($users as &$user){
                            if($user['real_email']){
                                $prm = array(
                                    'subject' => 'Unfortunately your request can’t be produced.',
                                    'from' => $user['tmp']['fr'],
                                    'to' => $user['email'],
                                    'date' => date('j M Y', strtotime($user['dt'])),
                                    'car' => $request['full_name'],
                                    'UserID' => $user['id'],
                                    'tag' => 'request-cant' . $user['tmp']['tag']
                                );
                                if(!empty($user['name'])){
                                    $prm['name'] = $user['name'];
                                }
                                (new postmarkMailer('request_cant' . $user['tmp']['t']))->mail($prm);
                            }
                        }
                    }
                    $requestVoteModel->updateByField('request_id', $rid,[
                        'status' => 17,
                        'update_datetime' => NULL
                    ]);
                    noxSystem::location('/administrator/prints/?section=request&action=crm&status=1&update_count=1&days=90&want_pay=3');
                }
                $this->caption = 'Не можем';
                $admin = $this->haveRight('control');
                $this->addVar('admin', $admin);
            }else{
                noxSystem::location('/administrator');
            }
        }else{
            noxSystem::location('/administrator');
        }
    }

    /**
     * Рассылка уведомлений на сделанный вектор
     */
    public function actionReady(){
        if(isset($_GET['id'])) {
            $id = $_GET['id'];
            $topOption = isset($_GET['option']) && ($_GET['option'] == 'top');
            $vector = $this->model->getById($id);
            if($vector){
                $this->addVar('vector', $vector);
                $paymentModel = new paymentModel();
                $list = ($usersPay = $paymentModel->where([
                    'ready' => '0',
                    'purchase_id' => $id,
                    'status' => 'approved'
                ])->fetchAll(null, 'user_id'));
                if($list){
                    $users_model = noxSystem::getUserModel();
                    $users = $users_model->select('id, email, name, user_type')->where('id', $list)->fetchAll();
                    if(sizeof($users) > 0){
                        foreach ($users as &$user) {
                            $user['real_email'] = ($user['user_type'] != 'facebook')
                                || (($email_ar = explode('@', $user['email'])) && $email_ar[1] != 'facebook.com');
                        }
                        $this->addVar('users', $users);
                    }
                }

                //Сохраняем
                if (isset($_POST['submit'])) {
                    if(isset($_POST['email']) && isset($users) && sizeof($users) > 0){
                        $email_car = $vector['full_name'];
                        $prm = json_decode($vector['prm']);
                        if($topOption){
                            $email_subject = 'Top View for your ' . $prm->year . ' ' . $prm->make . ' ' . $vector['name'] .
                                ' blueprint is ready - Download it now';
                            $email_template = 'vector_top_link';
                        } else {
                            $email_subject = $prm->year . ' ' . $prm->make . ' ' . $vector['name'] .
                                ' vector blueprint is ready - Download it now';
                            $email_template = 'vector_link';
                        }
                        $email_link = 'http://' . noxSystem::$domain . Prints::createUrlForItem([
                                'id' => $id,
                                'url' => $vector['url']
                            ], Prints::VECTOR);
                        $email_title = $vector['full_name'] . ' blueprints';
                        foreach ($users as &$user){
                            if($user['real_email']){
                                $prm = array(
                                    'subject' => $email_subject,
                                    'to' => $user['email'],
                                    'car' => $email_car,
                                    'link' => $email_link,
                                    'title' => $email_title,
                                    'UserID' => $user['id']
                                );
                                if(!empty($user['name'])){
                                    $prm['name'] = $user['name'];
                                    $prm['subject'] .= ' - ' . $user['name'];
                                }
                                (new postmarkMailer($email_template))->mail($prm);
                            }
                        }
                    }
                    $paymentModel->updateByField([
                        'purchase_id' => $id,
                        'status' => 'approved'
                    ], false, ['ready' => 1]);
                    noxSystem::location('/administrator/payment/?section=preorder');
                }
                $this->caption = 'Рассылка уведомлений об исполнении заказа';
            }else{
                noxSystem::location('/administrator');
            }
        }else{
            noxSystem::location('/administrator');
        }
    }

    /**
     * Просмотр
     */
    public function actionView()
    {
        $id = @$_GET['id'];
        $ar = $this->model->getById($id);
        if(!$ar) return 401;

        $ar['img_src'] = noxConfig::getConfig()['mediaSrc'];
        $ar['img_preview'] = noxSystem::$media -> src($ar['preview']);

        $categoryId = $ar['class_id'];
        $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$categoryId]['db_table']);
        $form = new noxModelForm($categoryModel);

        $this->addVar('category', $this->categories[$ar['class_id']]);
        $tmp = new noxDbQuery();
        $tmp->exec('SELECT `vector_id`, `name_full`, `url` FROM `prints_set_vector` LEFT JOIN `prints_set` ON `prints_set`.`id` = `prints_set_vector`.`set_id` WHERE `vector_id` = ' . $id);
        $sets = $tmp->fetchAll('vector_id');
        if(isset($sets[$id])){
            $ar['set']['url'] = $sets[$id]['url'];
            $ar['set']['name'] = $sets[$id]['name_full'];
        }
        $this->addVar('ar', $ar);

        $form->onlyFields = true;
        $categoryData = $categoryModel->getById($ar['item_id']);
        $this->addVar('categoryData', $categoryData);
        $form->setValues($categoryData);
        if(isset($form->model->fields['make_id'])) {
            $form->model->fields['make_id']['sql_where'] = 'class_id = ' . $categoryId;
        }

        if(isset($_SERVER['HTTP_REFERER'])) {
            $this->addVar('locationBack', $_SERVER['HTTP_REFERER']);
        }
        $make = $this->makeModel->reset()->where('id', $categoryData['make_id'])->fetch();
        $title = join(' ', [$make['name'], $ar['name'], $ar['name_version'], $ar['name_spec'], $categoryData['body'], $categoryData['year']]);
        $this->addVar('title', $title);
        $this->caption = $title;
    }

    /**
     * Редактирование
     */
    public function actionEdit()
    {
        $exts = explode(',', str_replace('\'', '', $this->model->fields['ext']['length']));
        $this->addVar('exts', $exts);
        $id = @$_GET['id'];

        $ar = $this->model->getById($id);
        if(!$ar) return 401;
        $ar['ext'] = explode(',', $ar['ext']);
        $ar['img_src'] = noxConfig::getConfig()['mediaSrc'];
        $ar['img_preview'] = noxSystem::$media -> src($ar['preview']);

        $categoryId = $ar['class_id'];
        $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$categoryId]['db_table']);
        $form = new noxModelForm($categoryModel);

        if (isset($_POST['submit']) || isset($_POST['submit_with_view'])) {
            $new = $_POST['new'];

            $categoryData = $_POST[$form->getFormInputName()];

            unset($categoryData['id']);
            $categoryModel->updateById($ar['item_id'], $categoryData);
            $new['class_id'] = $categoryId;
            //Обновление информации о файле
            $new['update_date'] = noxDate::toSql();
            $new['updated_by'] = $this->editorID;
            $new['views'] = isset($new['views']) ? Views::array2int($new['views']) : 0;
            $new['ext'] = (isset($new['ext'])) ? implode(',', $new['ext']) : '';
            $new['prepay'] = isset($new['prepay']) ? intval($new['prepay']) : 0;
            if(!$new['prepay']){
                $operImages = new systemAdministratorImagesAction();
                $operImages->check208($new['preview']);
            }
            $new['full_name'] = Prints::generateFullName($new, $categoryData);
            $new['prm'] = Prints::generatePrm($categoryData);
            $new['sort_name'] = Prints::generateSortName($new, $categoryData);
            $new['url'] = Prints::generateSEOUrl($new, $categoryData, Prints::VECTOR);

            $oldVector = $this->model->getById($id);
            $this->model->updateById($id, $new);

            //Пересчитываем количество чертежей марки
            if(isset($categoryData['make_id'])) {
                $itemsId = $categoryModel->select('id')->where('make_id', $categoryData['make_id'])->fetchAll(false, 'id');
                if(!$itemsId) {
                    $count = 0;
                }
                else {
                    $count = $this->model->reset()->where(['class_id' => $categoryId, 'item_id' => $itemsId])->count();
                }
                $this->makeModel->updateById($categoryData['make_id'], array('vectors_count' => $count));
                $form->addFieldsParams(['make_id' => ['class' => 'js-change-data-folder']]);
            }

            $set_id = (new printsSetModel())->updateSets($new, $oldVector);
            if(isset($_POST['locationBack'])) {
                noxSystem::location($_POST['locationBack']);
            }
            else {
                noxSystem::location('/administrator/prints/?section=vector&filter[category_id]=' . $categoryId
                    . '&filter[make_id]=' . $categoryData['make_id']
                    . ($set_id > 0 ? ('&filter[set_id]=' . $set_id) : '')
                    . (isset($_POST['submit_with_view']) ? ('view=' . $id) : '')
                );
            }
        }

        $this->addVar('category', $this->categories[$ar['class_id']]);
        $this->addVar('ar', $ar);
        $this->caption = 'Редактирование вектора';

        $form->onlyFields = true;
        $categoryData = $categoryModel->getById($ar['item_id']);
        $this->addVar('categoryData', $categoryData);
        $form->setValues($categoryData);
        if(isset($form->model->fields['make_id'])) {
            $form->model->fields['make_id']['sql_where'] = 'class_id = ' . $categoryId;
            $form->addFieldsParams(['make_id' => ['class' => 'js-change-data-folder']]);
        }
        $this->addVar('f', $form);

        if(isset($_SERVER['HTTP_REFERER'])) {
            $this->addVar('locationBack', $_SERVER['HTTP_REFERER']);
        }
        $this->addVar('makeUrls', $this->makeModel->reset()->where('class_id', $categoryId)->fetchAll('id', 'url'));
        $admin = $this->haveRight('control');
        $this->addVar('admin', $admin);
    }

    /*
	 * Удаление чертежа
	 */
    public function actionDelete()
    {
        $id = getParam($_GET['id']);
        if (!$id) {
            return 400;
        }

        $old = $this->model->getById($id);
        $this->model->deleteById($id);

        //TODO: Пишем в лог
        $cat = $this->categories[$old['class_id']];
        $catModel = new noxModel(false, 'prints_class_' . $cat['db_table']);
        $cat['id'] && $catModel->deleteById($cat['id']);

        if(isset($catModel->fields['make_id'])) {
            //Пересчитываем количество чертежей марки
            $count = $catModel->countByField('make_id', $old['make_id']);
            $this->makeModel->updateById($old['make_id'], array('blueprints_count' => $count));
        }

        (new printsSetModel())->clear();

        if ($this->ajax()) {
            return 200;
        } else {
            noxSystem::location('?section=vector');
        }
    }

    public function actionPriceReplace() {
        if(isset($_POST['p1']) && isset($_POST['p2'])) {
            $p1 = $_POST['p1'];
            $p2 = $_POST['p2'];

            if(is_numeric($p1) && is_numeric($p2)) {
                $this->model->exec("UPDATE `prints_vector` SET `price`= \"{$p2}\" WHERE `price` = \"{$p1}\"");
                $this->addVar('r', $this->model->affectedRows());
            }
        }
    }

    public function actionComment(){
        if (!$this->haveRight('control')) {
            return 401;
        }
        if (!isset($_GET['id']) || !isset($_GET['comment'])) {
            return 400;
        }
        $this->model->updateByField('id', $_GET['id'], [
            'comment' => urldecode($_GET['comment'])
        ]);
    }

    public function actionMultiEdit()
    {
        if (isset($_POST['submit'])) {
            $new = current($_POST['new']);
            $id  = key($_POST['new']);

            $current = $this->model->getById($id);

            $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$current['class_id']]['db_table']);
            $form = new noxModelForm($categoryModel);

            $categoryData = $_POST[$form->getFormInputName()];

            unset($categoryData['id']);
            $categoryModel->updateById($current['item_id'], $categoryData);

            //Обновление информации о файле
            $new['update_date'] = noxDate::toSql();
            $new['updated_by'] = $this->editorID;
            $new['views'] = isset($new['views']) ? Views::array2int($new['views']) : 0;

            $new['filename'] = str_replace('/nox-data/', '/', $new['filename']);

            $this->model->updateById($id, $new);

            //TODO: ведем лог

            //Пересчитываем количество чертежей марки
            if(isset($categoryData['make_id'])) {
                $count = $categoryModel->countByField('make_id', $categoryData['make_id']);
                $this->makeModel->updateById($categoryData['make_id'], array('blueprints_count' => $count));
            }
        }

        $this->title = $this->caption = 'Векторы';

        $onPage = 20;
        $count = $this->model->reset()->count();
        $pagesCount = intval($count/$onPage)+1;
        $page = min(max(@getParam($_GET['page'], 1), 1), $pagesCount);

        $this->addVar('pagesCount', $pagesCount);
        $this->addVar('page', $page);

        $this->addVar('res', $this->model->limit(($page-1)*$onPage, $onPage)->order('id DESC')->fetchAll('id'));
    }
}
