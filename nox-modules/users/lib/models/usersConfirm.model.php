<?php

class usersConfirmModel extends noxModel {

    public $table = 'nox_user_confirm';

    public static function generateRegistrationConfirmCode() {
        return noxSystem::$userControl->userModel->generatePassword(48);
    }

    public function confirmUser($code, $unsubscribe = false) {
        $ar = $this->where([
            'code' => $code,
            'action' => ['registration', 'resend']
        ])->fetch();
        if(!$ar) {
            return -1;
        }
        $userModel = &noxSystem::$userControl->userModel;
        if($userModel->getById($ar['user_id'])) {
            if($unsubscribe){
                $up = ['registration_status' => 'unsubscribe'];
                $res = 0;
            } else {
                $up = [
                    'registration_status' => 'success_confirm',
                    'confirm_email_date' => date('Y-m-d H:i:s')
                ];
                $res = 1;
            }
            $userModel->updateById($ar['user_id'], $up);
            $this->deleteById($ar['id']);
            return $res;
        }
        else {
            return -2;
        }
    }

    public function resetPassUser($code, $unsubscribe = false) {
        $ar = $this->where([
            'code' => $code,
            'action' => 'remind_password'
        ])->fetch();
        if(!$ar) {
            return -1;
        }
        $userModel = &noxSystem::$userControl->userModel;
        if($userModel->getById($ar['user_id'])) {
            if($unsubscribe){
                $userModel->updateById($ar['user_id'], [
                    'registration_status' => 'unsubscribe'
                ]);
                $this->deleteById($ar['id']);
                $res = 0;
            } else {
                $res = (int)$ar['user_id'];
            }
            return $res;
        } else {
            return -2;
        }
    }
}