<?php

class usersDefaultConfirmEmailAction extends noxAction {
    public $cache = false;

    public function execute() {
        if(!noxSystem::$userControl->authorization() || isset($_GET['fb'])){
            $model = new usersConfirmModel();
            if(isset($_GET['c'])) {
                $resp = $model->confirmUser($_GET['c'], isset($_GET['u']));

                if ($resp >= 0) {
                    if ($resp > 0) {
                        $ar = $model->where('code', $_GET['c'])->fetch();
                        $user = noxSystem::$userControl->userModel->getById($ar['user_id']);

                        (new postmarkMailer('registration'))->mail([
                            'to' => $user['email'],
                            'UserID' => $user['id']
                        ]);

                        noxSystem::$userControl->login($user['email'], '', 'email', true);
                        $url = (new usersUserActions())->checkLog($ar['user_id']);
                        if (isset($_GET['fb'])) {
                            setcookie('confirm_email_fb', !empty($url) ? $url : 'true', time() + 1000, '/');
                        } else {
                            setcookie('confirm_email', !empty($url) ? $url : 'true', time() + 1000, '/');
                        }
                    } else {
                        setcookie('unsubscribe', 'true', time() + 1000, '/');
                    }
                }
            } elseif(isset($_GET['r'])) {
                $resp = $model->resetPassUser($_GET['r'], isset($_GET['u']));
                if ($resp >= 0) {
                    if ($resp > 0) {
                        setcookie('reset_pass', $resp, time() + 1000, '/');
                    } else {
                        setcookie('unsubscribe', 'true', time() + 1000, '/');
                    }
                }
            }
        }

        noxSystem::location('/');
    }
}