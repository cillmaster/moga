<?php
/**
 * Страница вывода категории чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsDebugDebugAction extends noxAction
{
    public function execute()
    {
        $ch = curl_init('https://www.googleapis.com/oauth2/v3/token');

        $fields = [
            'access_token' => '',
            'access_token' => '',
            'access_token' => '',
        ];
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);


        $resp = curl_exec($ch);
        _d($resp);
    }
}
