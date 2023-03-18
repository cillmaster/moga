<?php

class usersDefaultProfileAction extends noxThemeAction
{
    public $cache = false;

    public function execute()
    {
        $this->title = $this->caption = 'My Downloads';

        noxSystem::$userControl->authorization();

        $modelUser = noxSystem::$userControl->getUserModel();

        $userId = noxSystem::$userControl->getUserId();
        if (!$userId){
            return 401;
        }
        $array = noxSystem::$userControl->getUser();

        $this->addVar('new', $array);
        $this->addVar('userId', $userId);

        $dbQuery = new noxDbQuery();
        $sql = 'SELECT `prints_vector`.*, `payment`.`ready` '
            . 'FROM `prints_vector` '
            . 'LEFT JOIN `payment` ON `prints_vector`.`id` = `payment`.`purchase_id` '
            . 'WHERE `user_id` = ' . noxSystem::getUserId() . ' AND `status` = \'approved\' '
            . 'ORDER BY `datetime` DESC';
        $dbQuery->exec($sql);
        if($purchases = $dbQuery->fetchAll()) {
            foreach ($purchases as &$row)
                $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
            $this->addVar('purchases', $purchases);
        }

        if (isset($_POST['email']) && isset($_POST['name']))
        {
            //Изменяем точно профиль текущего пользователя
            $userId = noxSystem::$userControl->getUserId();

            $password = $_POST['password'];
            //Получаем хеш
            $hash = noxUserModel::hash($password);

            $new['name'] = htmlspecialchars(trim($_POST['name']));
            $new['full_name'] = $new['name'];
            $new['email'] = $new['login'] = htmlspecialchars(trim($_POST['email']));

            if (empty($new['name']) || empty($new['email']))
            {
                $this->addVar('error', _t('You did not fill in some fields.'));
                $this->addVar('new', $new);
            } else
            {
                $modelUser->updateById($userId, $new);

                if (!empty($password))
                {
                    //Отсылаем письмо о смене пароля с email'ом
                    $mail = new noxMail($this->moduleFolder . '/templates/defaultChangePasswordEmail.html');
                    $mail->to($new['email'])->addVar('password', $password)->addVar('email', $new['email']);

                    //Если письмо отправилось
                    if ($mail->send())
                    {
                        //Записываем хеш
                        $modelUser->updateById($userId, array('password' => $hash));
                        //Авторизуем пользователя
                        noxSystem::$userControl->login($new['email'], $password);
                    } else
                    {
                        return 500;
                    }
                }

                noxSystem::location();
            }
        }
    }
}

?>