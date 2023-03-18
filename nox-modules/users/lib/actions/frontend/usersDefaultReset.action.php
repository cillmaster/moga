<?php

class usersDefaultResetAction extends noxThemeAction {
    public $cache = false;

    public function execute(){
        if (noxSystem::$userControl->authorization()) {
            //Если пользователь уже авторизован, то переводим его на профиль
            noxSystem::location(noxSystem::$moduleUrl.'/profile');
        }

        if (isset($_POST['reset'])) {
            $json = new noxJson();
            //Модель пользователей
            $modelUser = noxSystem::$userControl->getUserModel();
            $usersConfirmModel = new usersConfirmModel();
            $user = $_POST['reset'];
            if(isset($user['email'])) {
                $user['email'] = htmlspecialchars(trim($user['email']));
                if(filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    if ($mUser = $modelUser->getByField([
                        'email' => $user['email'],
                        'user_type' => 'email'
                    ])) {
                        if ($mUser['registration_status'] == 'success_confirm') {
                            $userId = $mUser['id'];
                            if ($mUserConfirm = $usersConfirmModel->where([
                                'user_id' => $userId,
                                'action' => 'remind_password'
                            ])->fetch()) {
                                $json->error = 'We have already sent an email to reset your password before. Please check your email to find it.';
                            } else {
                                do {
                                    $code = usersConfirmModel::generateRegistrationConfirmCode();
                                } while ($usersConfirmModel->where('code', $code)->fetch());
                                $confirmLink = 'http://' . noxSystem::$domain . '/users/confirm?r=' . $code;
                                $usersConfirmModel->insert([
                                    'user_id' => $userId,
                                    'code' => $code,
                                    'action' => 'remind_password'
                                ]);

                                $prmEmail = [
                                    'to' => $user['email'],
                                    'confirmLink' => $confirmLink,
                                    'UserID' => $userId
                                ];
                                if($mUser['name'] != ''){
                                    $prmEmail['name'] = $mUser['name'];
                                }
                                (new postmarkMailer('reset_email'))->mail($prmEmail);
                                $json->message = 'reset_email';
                            }
                        } else {
                            $json->error = 'This email address have not been confirmed yet. Please use your confirmation link and confirm your email before reset password.';
                        }
                    } else {
                        $json->error = 'This email address does not exist. Please check if it is typed correctly or create new account using this email.';
                    }
                } else {
                    $json->error = 'Wrong email address. Please edit your email address to continue.';
                }
            } elseif(isset($user['password']) && isset($user['id'])){
                if( $user['password'] === $_POST['confirm-password'] ) {
                    $modelUser->updateById($user['id'], [
                        'password' => noxUserModel::hash($user['password'])
                    ]);
                    $usersConfirmModel->deleteByField([
                        'user_id' => $user['id'],
                        'action' => 'remind_password'
                    ]);
                    $user = $modelUser->getById($user['id']);
                    noxSystem::$userControl->login($user['email'], '', 'email', true);
                    $url = (new usersUserActions())->checkLog($user['id']);
                    setcookie('reset_pass_fin', !empty($url) ? $url : 'true', time() + 1000, '/');
                }
                else {
                    $json->error = 'Check passwords and confirm!';
                }
            } else {
                $json->error = 'Unknown error!';
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