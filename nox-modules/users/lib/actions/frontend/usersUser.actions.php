<?php

class usersUserActions extends noxThemeActions
{
    public $cache = false;

    public function checkLog($userID){
        $url = '';
        $requestVectorModel = new printsRequestVectorModel();
        $requestVectorModel->checkRequestCodes($userID);
        if(isset($_COOKIE['myLastAction'])) {
            $action = explode('|', $_COOKIE['myLastAction']);
            setcookie('myLastAction', '', time() - 1000, '/');
            switch ($action[0]){
                case 'buy':
                    $url = Prints::createUrlForItem((new printsVectorModel())->getById($action[1]), Prints::VECTOR);
                    setcookie('forceBuy', $action[1], 0, '/');
                    break;
                case 'vote':
                    (new printsRequestVoteModel())->vote($action[1], Prints::REQUEST_VECTOR, intval($action[2]));
                    $url = Prints::createUrlForItem($requestVectorModel->getById($action[1]), Prints::REQUEST_VECTOR);
                    break;
                case 'blueprint_force':
                    $url = '/requests/create/vector/from/blueprint/' . $action[1];
                    break;
            }
        }
        (new paymentCartModel())->mergeCartDetails();
        return $url;
    }

    /**
     * Логинит пользователя
     *
     */
    public function actionLogin()
    {
        $userModel = noxSystem::$userControl->userModel;

        $user_prm = array(
            'ip' => $_SERVER['HTTP_X_REAL_IP'],
            'lng' => explode('-', explode(';', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0])[0])[0]
        );

        if(!isset($_POST['user_type'])) {
            $_POST['user_type'] = 'email';
        }
        if($_POST['user_type'] === 'facebook') {
            \Facebook\FacebookSession::setDefaultApplication(FACEBOOK_APP_ID, FACEBOOK_APP_SECRET);
            $session = new \Facebook\FacebookSession($_POST['authResponse']['accessToken']);
            if($session) {
                try {
                    $me = (new \Facebook\FacebookRequest(
                        $session, 'GET', '/me'
                    ))->execute()->getGraphObject(\Facebook\GraphUser::className());

                    $user = $userModel->where(['login' => $me->getId(), 'user_type' => 'facebook'])->fetch();
                    if(!$user && isset($_POST['email'])) {
                        $email = htmlspecialchars(trim($_POST['email']));
                        $userModel->insert([
                            'login' => $me->getId(),
                            'email' => $email,
                            'password' => $userModel::hash($me->getId()),
                            'name' => $me->getName(),
                            'registration_status' => 'wait_confirm',
                            'user_type' => 'facebook',
                            'utm_value' => ((isset($_COOKIE['nox_utm'])) ? $_COOKIE['nox_utm'] : ''),
                            'prm' => json_encode($user_prm)
                        ]);

                        $userId = $userModel->insertId();

                        $usersConfirmModel = new usersConfirmModel();
                        do {
                            $code = usersConfirmModel::generateRegistrationConfirmCode();
                        } while($usersConfirmModel->where('code', $code)->fetch());

                        $confirmLink = 'https://' . noxSystem::$domain . '/users/confirm?c=' . $code . '&fb=true';
                        $usersConfirmModel->insert([
                            'user_id' => $userId,
                            'code' => $code
                        ]);

                        (new postmarkMailer('confirm_email'))->mail([
                            'to' => $email,
                            'name' => $me->getName(),
                            'confirmLink' => $confirmLink,
                            'UserID' => $userId
                        ]);

                        if(isset($_COOKIE['_um']) && ($_COOKIE['_um'] === 'bzu')) {
                            (new noxUserGroupsModel())->insert(['user_id' => $userId, 'group_id' => Users::businessGroupId]);
                        }
                    }

                    if(noxSystem::$userControl->login($me->getId(), $me->getId(), 'facebook')){
                        if($url = $this->checkLog(noxSystem::getUserId())){
                            header('Content-type: application/json; charset: utf-8', true);
                            echo '{"url":"' . $url . '"}';
                            return 200;
                        }
                    }
                } catch (\Facebook\FacebookRequestException $e) {

                }
            }

            exit;
        }
        elseif(($_POST['user_type'] === 'google') && isset($_POST['user']) && isset($_POST['auth'])) {
            $me = $_POST['user'];
            $auth = $_POST['auth'];

            $user = $userModel->where(['login' => $me['result']['id'], 'user_type' => $_POST['user_type']])->fetch();
            if(!$user) {

                $hasRealEmail = isset($me['result']['emails'][0]['value']);
                $userModel->insert([
                    'login' => $me['result']['id'],
                    'email' => (isset($me['result']['emails'][0]['value'])) ? $me['result']['emails'][0]['value'] : $me['result']['id'] . '@google.com',
                    'password' => $userModel::hash($me['result']['id']),
                    'name' => $me['result']['displayName'],
                    'registration_status' => 'success_confirm',
                    'confirm_email_date' => date('Y-m-d H:i:s'),
                    'user_type' => $_POST['user_type'],
                    'utm_value' => ((isset($_COOKIE['nox_utm'])) ? $_COOKIE['nox_utm'] : ''),
                    'prm' => json_encode($user_prm)
                ]);

                $userId = $userModel->insertId();
                if(isset($_COOKIE['_um']) && ($_COOKIE['_um'] === 'bzu')) {
                    (new noxUserGroupsModel())->insert(['user_id' => $userId, 'group_id' => Users::businessGroupId]);
                }
                if($hasRealEmail) {
                    (new postmarkMailer('registration'))->mail([
                        'to' => $me['result']['emails'][0]['value'],
                        'UserID' => $userId
                    ]);
                }

            }
            if(noxSystem::$userControl->login($me['result']['id'], $me['result']['id'], $_POST['user_type'])){
                if($url = $this->checkLog(noxSystem::getUserId())){
                    header('Content-type: application/json; charset: utf-8', true);
                    echo '{"url":"' . $url . '"}';
                    return 200;
                }
            }
            exit;
        }

        if(isset($_POST['login'])) $_POST['email'] = $_POST['login'];
        $ok = noxSystem::$userControl->login(@$_POST['email'], @$_POST['password']);

        if (!noxSystem::$userControl->authorization())
        {
            header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
            header('Status: 401 Unauthorized');

            echo '<h2>[`Wrong login and password`]</h2>
			<form method="POST" action="/users/login" class="w350">
				<p><label>[`Login`]:</label> <span><input type=text name="email" required="required" /></span></p>
				<p><label>[`Password`]:</label> <span><input type="password" name="password" required="required" /></span></p>
				<input type="submit" value="[`Log in`]" />
			</form>
			';
        } else
        {
            $url = $this->checkLog(noxSystem::getUserId());
            if($this->ajax()) {
                header('Content-type: application/json; charset: utf-8', true);
                echo '{"url":"' . $url . '"}';
                return 200;
            }
            !empty($url) ? noxSystem::location($url) : noxSystem::locationBack();
        }
    }

    /**
     * Закрывает сессию пользователя
     *
     */
    public function actionLogout()
    {
        noxSystem::$userControl->logout();
        noxSystem::locationBack();
    }

    /**
     * Удаление аватара пользователя
     * @return int
     */
    public function actionAvatarDelete()
    {
        noxSystem::$userControl->authorization();

        $userId = noxSystem::$userControl->getUserId();
        if (!$userId)
        {
            return 401;
        }

        $filename = $GLOBALS['vars']['user']['photo'];

        //Если нет файла, то просто возвращаем удачный результат
        if (!$filename)
        {
            return 200;
        }
        //Обновляем пользователя: у него больше нет аватара :`(
        noxSystem::$userControl->getUserModel()->updateById($userId, array('photo' => ''));

        //Разбираем URL, чтобы узнать на каком сервере находится картинка
        $url = parse_url($filename);

        //Если файл на текущем сервере
        if ($url['host'] == $_SERVER['SERVER_NAME'])
        {
            //То определяем путь к файлу и удаляем
            noxFileSystem::delete(noxFileSystem::getRealPathFromUrl($url['path']));
            //Возвращаем код ОК
            return 200;
        }
    }

    /**
     * Загрузка аватара
     * @return int
     */
    public function actionAvatarUpload()
    {
        noxSystem::$userControl->authorization();

        $userId = noxSystem::$userControl->getUserId();
        if (!$userId) {
            return 401;
        }

        $filename = $GLOBALS['vars']['user']['photo'];

        if (isset($_FILES['file'])) {
            $ImageToLoad = $_FILES['file']['tmp_name'];

            //Загружаем изображение
            $image = @ImageCreateFromPNG($ImageToLoad);
            if (!$image) {
                $image = @ImageCreateFromGIF($ImageToLoad);
            }
            if (!$image) {
                $image = @ImageCreateFromJPEG($ImageToLoad);
            }
            if (!$image) {
                $image = @ImageCreateFromWBMP($ImageToLoad);
            }
            //Прислано не изображение
            if (!$image) {
                return 400;
            }

            /* Настройки конечного изображения */
            $fitwidth = 130; //Ширина изображения
            $fitheight = 130; //Высота изображения
            $dest_filename = '/nox-data/users/avatars/' . $userId . '.jpg';

            //Размеры изображения
            $w = imagesx($image);
            $h = imagesy($image);

            $dest = imagecreatetruecolor($fitwidth, $fitheight);
            imagefill($dest, 0, 0, imagecolorallocate($dest, 255, 255, 255));

            $ratio = $w / $fitwidth;
            $w_dest = round($w / $ratio);
            $h_dest = round($h / $ratio);
            if ($h_dest <= $fitheight) {
                $ratio = $h / $fitheight;
                $w_dest = round($w / $ratio);
                $h_dest = round($h / $ratio);
                imagecopyresampled($dest, $image, (($fitwidth - $w_dest) / 2), 0, 0, 0, $w_dest, $h_dest, $w, $h);
            } else {
                imagecopyresampled($dest, $image, 0, (($fitheight - $h_dest) / 2), 0, 0, $w_dest, $h_dest, $w, $h);
            }
            ImageDestroy($image);

            #Вывести изображение, затем уничтожив его
            ImageJPEG($dest, '.' . $dest_filename, 100);
            ImageDestroy($dest);

            $dest_filename = 'http://' . $_SERVER['SERVER_NAME'] . $dest_filename;

            //Выводим адрес изображения
            echo $dest_filename;

            //Записываем в БД
            noxSystem::$userControl->getUserModel()->updateById($userId, array('photo' => $dest_filename));
        }
    }


    /**
     * Смена пароля
     * @return int
     */
    public function actionPasswordChange()
    {
        noxSystem::$userControl->authorization();

        $userId = noxSystem::$userControl->getUserId();
        if (!$userId)
        {
            return 401;
        }

        if (isset($_POST['password']))
        {
            //Сохраняем новый пароль
            $modelUser = noxSystem::$userControl->getUserModel();

            $user = $GLOBALS['vars']['user'];

            $password = $_POST['password'];

            //Получаем хеш
            $hash = addslashes(noxUserModel::hash($password));

            //Отсылаем письмо о смене пароля с email'ом
            $mail = new noxMail($this->moduleFolder . '/templates/default/defaultChangePasswordEmail.html');
            $mail->from('noreply@businesschampions.ru')->to($user['email'])->addVar('password', $password);

            //Если письмо отправилось
            if ($mail->send())
            {
                //Записываем хеш
                $modelUser->updateById($userId, array('password' => $hash));
            } else
            {
                return 500;
            }

            $this->addVar('change', true);
        } else
        {
            $this->addVar('change', false);
        }
    }

    public function actionRemindPassword()
    {
        if (isset($_POST['email']))
        {
            //Сохраняем новый пароль
            $modelUser = noxSystem::$userControl->getUserModel();
            $email = htmlspecialchars(strip_tags(@$_POST['email']));

            $user = $modelUser->getByField('email', $email);
            $userId = $user['id'];
            $password = $modelUser::generatePassword();
            //Получаем хеш
            $hash = noxUserModel::hash($password);

            if (empty($email) || empty($password))
            {
                $this->addVar('error', _t('You did not fill in some fields.'));
            }
            elseif (!$user)
            {
                $this->addVar('error', _t('No user with this email!'));
            }
            else
            {
                //Отсылаем письмо о смене пароля с email'ом
                $mail = new noxMail($this->moduleFolder . '/templates/defaultChangePasswordEmail.html');
                $mail->to($user['email'])->addVar('email', $email)->addVar('password', $password);

                //Если письмо отправилось
                if ($mail->send())
                {
                    //Записываем хеш
                    $modelUser->updateById($userId, array('password' => $hash));
                    echo _t('Password has been successfully changed! A new password has been sent to your email.');
                    noxSystem::locationAfterTime(noxSystem::$baseUrl.'/', 2);
                } else
                {
                    return 500;
                }
            }

        } else
        {
            $this->addVar('change', false);
        }
    }
}
