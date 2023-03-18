<?php
/**
 * Действия для управления пользователями
 *
 * @author     Тулаев Сергей Сергеевич <odminchek@yandex.ru>
 * @version    1.0
 * @package    users
 * @subpackage admin
 */
class usersAdministratorActions extends noxThemeActions
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Пользователи';

    /**
     * Действие по-умолчанию
     *
     * @return int
     */
    public function actionDefault()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('users'))
        {
            return 401;
        }

        $model = noxSystem::getUserModel();

        $mapSearch = [
            ['type' => 'email', 'where' => '`email` LIKE "%###%"'],
            ['type' => 'name', 'where' => '`name` LIKE "%###%"'],
            ['type' => 'id', 'where' => '`id` = ###'],
        ];

        if(!isset($_GET['search_type'])){
            $_GET['search_type'] = 0;
        }
        $this->addVar('type', $mapSearch[$_GET['search_type']]['type']);

        if($_GET['search'] != ''){
            $model->where(str_replace('###', $model->escape($_GET['search']), $mapSearch[$_GET['search_type']]['where']));
            unset($_GET['payment']);
        }

        $page = $_GET['page'];

        if(isset($_GET['payment'])){
            $paymentModel = new paymentModel();
            $this->addVar('pager', (new kafPager())->create($paymentModel->getCountPurchases(), $onPage = 100, 10));
            $userPayment = $paymentModel->getUsersByPurchases(($page - 1) * $onPage, $onPage);
            $userData = $model->where('id', array_keys($userPayment))->fetchAll('id');
            $res = [];
            foreach ($userPayment as $id => $prm){
                $tmp = $userData[$id];
                $tmp['p_count'] = $prm['c'];
                $tmp['p_sum'] = number_format($prm['s'], 2);
                $res[] = $tmp;
            }
            $this->addVar('payment', true);
        } else {
            $this->addVar('pager', (new kafPager())->create($model->count(), $onPage = 100, 10));
            $res = $model->order('registration_date DESC')->limit(($page - 1) * $onPage, $onPage)->fetchAll();
        }

        foreach ($res as &$row){
            if(isset($row['prm']) && $row['prm'] !== ''){
                $tmp = json_decode($row['prm']);
                $row['prm'] = array();
                foreach ($tmp as $key => $value){
                    $row['prm'][$key] = $value;
                }
            }else{
                unset($row['prm']);
            }
        }

        $this->addVar('res', $res);
    }

    public function actionProfile()
    {
        //Проверяем, есть ли у пользователя право

        if (!$this->haveRight('users')) {
            return 401;
        }

        $this->caption = 'Профиль пользователя';

        $user_id = $_GET['id'];
        $user = noxSystem::getUserModel()->where('id', $user_id)->fetch();
        if(isset($user['prm']) && $user['prm'] !== ''){
            $tmp = json_decode($user['prm']);
            foreach ($tmp as $key => $value){
                $user['prm_' . $key] = $value;
            }
        }
        unset($user['prm']);
        unset($user['password']);
        unset($user['utm_value']);
        $this->addVar('user', $user);

        $payment = (new paymentModel())
            ->where('user_id', $user_id)
            ->order('`datetime` DESC')
            ->fetchAll('id');
        if(!empty($payment))
            $this->addVar('payment', $payment);

        $requests = (new printsRequestVectorModel())
            ->where('user_id', $user_id)
            ->order('`request_date` DESC')
            ->fetchAll('id');
        if(!empty($requests))
            $this->addVar('requests', $requests);

        $tmp = new noxDbQuery();
        $tmp->exec('SELECT `request_id`, `vote_datetime`, `want_pay`, `full_name` 
            FROM `prints_request_vote` 
            LEFT JOIN `prints_request_vector` ON `request_id` = `prints_request_vector`.`id` 
            WHERE `prints_request_vote`.`user_id` = ' . $user_id );
        $vote = $tmp->fetchAll();
        if(!empty($vote))
            $this->addVar('vote', $vote);


    }

        /**
     * Действие добавления
     *
     * @return int
     */
    public function actionAdd()
    {
        //Проверяем, есть ли у пользователя право

        if (!$this->haveRight('users'))
        {
            return 401;
        }

        $this->caption = 'Добавить пользователя';

        //Модели
        $model = noxSystem::getUserModel();
        $userGroupsModel = new noxUserGroupsModel();
        $groupModel = new noxGroupModel();

        //Если данные пришли
        if (isset($_POST['new']))
        {
            if (!empty($_POST['password']))
            {
                $_POST['new']['password'] = $hash = noxUserModel::hash($_POST['password']);

                //Отсылаем письмо о смене пароля с email'ом
                $mail = new noxMail($this->moduleFolder . '/templates/defaultChangePasswordEmail.html');
                $mail->to($_POST['new']['email'])->addVar('password', $_POST['password'])->addVar('email', $_POST['new']['email'])->send();

            }
			
            if ($model->insert($_POST['new']))
            {
                $id = $model->insertId();
                $userGroupsModel->deleteByField('user_id', $id);
                //Занимаемся правами
                foreach ($_POST['groups'] as $group_id => $v)
                {
                    $userGroupsModel->insert(array('user_id' => $id,
                                                   'group_id' => $group_id));
                }
                //Если форма сохранилась
                noxSystem::location('?section=administrator');
            }

            $ar = $_POST['new'];
        }
        else
        {
            //Задан ли параметр для копирования
            $id = getParam(@$this->params['get']['id'], 0);
            if ($id)
            {
                $ar = $model->getById($id);
                unset($ar['id']);
            } else
            {
                $ar = $model->getEmptyFields();
            }
        }

        $this->addVar('ar', $ar);

        //получаем список групп
        $this->addVar('groups', $groupModel->order('name')->fetchAll('id', 'name'));
        //Получаем группы, в которых есть пользователь
        $userGroups = array();
        if ($res = $userGroupsModel->getByUser($id))
        {
            foreach ($res as $g)
            {
                $userGroups[$g] = true;
            }
        }
        $this->addVar('userGroups', $userGroups);
        $this->templateFileName = $this->moduleFolder . '/templates/administratorEdit.html';
    }

    /**
     * Действие редактирования
     *
     * @return int
     */
    public function actionEdit()
    {
        //Проверяем, есть ли у пользователя право

        if (!$this->haveRight('users'))
        {
            return 401;
        }

        $this->caption = 'Редактировать пользователя';

        //Модели
        $model = noxSystem::getUserModel();
        $userGroupsModel = new noxUserGroupsModel();
        $groupModel = new noxGroupModel();

        //подтверждение email
        if(isset($_POST['confirm'])){
            $id = intval($_POST['id']);
            $model->updateById($id, [
                'registration_status' => 'success_confirm',
                'confirm_email_date' => date('Y-m-d H:i:s')
            ]);
        }

        //Если данные пришли
        if (isset($_POST['new']))
        {
            if (!empty($_POST['password']))
            {
                $_POST['new']['password'] = $hash = noxUserModel::hash($_POST['password']);

                //Отсылаем письмо о смене пароля с email'ом
                $mail = new noxMail($this->moduleFolder . '/templates/defaultChangePasswordEmail.html');
                $mail->addVar('password', $_POST['password'])->addVar('email', $_POST['new']['email']);
                $mail->to($_POST['new']['email'])->subject('Password change')->send();

            }

            $id = intval($_POST['id']);

            if ($model->updateById($id, $_POST['new']))
            {
                $userGroupsModel->deleteByField('user_id', $id);
                //Занимаемся правами
                foreach ($_POST['groups'] as $group_id => $v)
                {
                    $userGroupsModel->insert(array('user_id' => $id,
                        'group_id' => $group_id));
                }
                //Если форма сохранилась
                noxSystem::location('?section=administrator');
            }

            $ar = $_POST['new'];
        }
        else
        {
            //Задан ли параметр для копирования
            $id = getParam(@$this->params['get']['id'], 0);
            if ($id)
            {
                $ar = $model->getById($id);
            } else
            {
                return 400;
            }
        }

        $this->addVar('ar', $ar);

        //получаем список групп
        $this->addVar('groups', $groupModel->order('name')->fetchAll('id', 'name'));
        //Получаем группы, в которых есть пользователь
        $userGroups = array();
        if ($res = $userGroupsModel->getByUser($id))
        {
            foreach ($res as $g)
            {
                $userGroups[$g] = true;
            }
        }
        $this->addVar('userGroups', $userGroups);
    }

    /**
     * Действие удаления
     *
     * @return int
     */
    public function actionDelete()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('users'))
        {
            return 401;
        }

        //Модель страниц
        $model = new noxUserModel();

        $id = getParam(@$this->params['get']['id'], 0);
        if ($id)
        {
            $model->deleteById($id);
            return 200;
        } else
        {
            return 400;
        }
    }
}

?>