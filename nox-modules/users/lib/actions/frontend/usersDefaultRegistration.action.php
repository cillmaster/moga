<?php
/**
 * Действия регистрации пользователя
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    users
 * @subpackage default
 */

class usersDefaultRegistrationAction extends noxThemeAction
{
    public $cache = false;

    public function execute()
    {
        if (noxSystem::$userControl->authorization())
        {
            //Если пользователь уже авторизован, то переводим его на профиль
            noxSystem::location(noxSystem::$moduleUrl.'/profile');
        }

        if (isset($_POST['registration']))
        {
            $json = new noxJson();
            //Модель пользователей
            $modelUser = noxSystem::$userControl->getUserModel();
            $usersConfirmModel = new usersConfirmModel();
            $user = $_POST['registration'];
            $user['email'] = htmlspecialchars(trim($user['email']));

            if(isset($user['check-email-only'])){
                if(!filter_var($user['email'], FILTER_VALIDATE_EMAIL)){
                    $json->error = 'Wrong email address. Please edit your email address to continue.';
                }
            } elseif($mUser = $modelUser->getByField('email', $user['email'])) {
                if($mUser['registration_status'] == 'success_confirm'){
                    $json->error = 'This email is already in use. You can login using this email.';
                } elseif ((time() - strtotime($mUser['registration_date'])) < 86400 * 7){
                    $json->message = 'email_sent';
                } elseif($user['password'] === $_POST['confirm-password']) {
                    $userId = $mUser['id'];
                    $mUserConfirm = $usersConfirmModel->where('user_id', $userId)->fetch();
                    if($mUserConfirm['action'] == 'registration'){
                        $modelUser->updateById($userId, [
                            'password' => noxUserModel::hash($user['password']),
                            'name' => htmlspecialchars(trim($user['name']))
                        ]);
                        $name = $mUser['name'];
                        $code = $mUserConfirm['code'];
                        $usersConfirmModel->updateById($mUserConfirm['id'], ['action' => 'resend']);
                        $json->message = 'email_re_sent';
                    } else {
                        $json->error = 'This email is already in use. You can login using this email.';
                    }
                } else {
                    $json->error = 'Check passwords and confirm!';
                }
            } elseif(@$user['password'] && @$user['email']) {
                if(filter_var($user['email'], FILTER_VALIDATE_EMAIL)){
                    if( $user['password'] === $_POST['confirm-password'] ) {
                        $password = $user['password'];
                        $name = htmlspecialchars(trim($user['name']));
                        $user['password'] = noxUserModel::hash($password);
                        $user['login'] = $user['email'];
                        $user['name'] = $name;
                        $user['registration_status'] = 'wait_confirm';

                        if(isset($_COOKIE['nox_utm'])) {
                            $user['utm_value'] = $_COOKIE['nox_utm'];
                        }

                        $user_prm = array(
                            'ip' => $_SERVER['HTTP_X_REAL_IP'],
                            'lng' => explode('-', explode(';', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0])[0])[0]
                        );
                        $user['prm'] = json_encode($user_prm);

                        if (!$modelUser->insert($user))
                        {
                            return 500;
                        }
                        else {
                            $userId = $modelUser->insertId();
                            do {
                                $code = usersConfirmModel::generateRegistrationConfirmCode();
                            } while($usersConfirmModel->where('code', $code)->fetch());

                            (new printsRequestVectorModel())->checkRequestCodes($userId, true);
                            if(isset($_COOKIE['_um']) && ($_COOKIE['_um'] === 'bzu')) {
                                (new noxUserGroupsModel())->insert(['user_id' => $userId, 'group_id' => Users::businessGroupId]);
                            }
                            $json->message = 'confirm_email';
                        }
                    }
                    else {
                        $json->error = 'Check passwords and confirm!';
                    }
                } else {
                    $json->error = 'Wrong email address. Please edit your email address to continue.';
                }
            } else {
                $json->error = 'Enter email and password!';
            }

            if(isset($code) && isset($userId)){
                $confirmLink = 'https://' . noxSystem::$domain . '/users/confirm?c=' . $code;
                $usersConfirmModel->insert([
                    'user_id' => $userId,
                    'code' => $code
                ]);

                $prmEmail = [
                    'to' => $user['email'],
                    'confirmLink' => $confirmLink,
                    'UserID' => $userId
                ];
                if($name != ''){
                    $prmEmail['name'] = $name;
                }
                (new postmarkMailer('confirm_email'))->mail($prmEmail);
            }

            if($this->ajax()) {
                header('Content-type: application/json; charset: utf-8', true);
                if(isset($json->error)) {
                    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
                    header('Status: 400 Bad Request');
                }
                echo $json;
                exit;
            }
        }
    }
}