<?php

class utmModel extends noxModel
{
    public $table = 'utm';
    public $utm = [];

    public static $params = [
        'utm_source',
        'utm_medium',
        'utm_term',
        'utm_content',
        'utm_campaign'
    ];

    public function insertUtm() {
        if(!empty($this->utm)) {
            if(isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']) {
                $this->utm['referrer'] = $_SERVER['HTTP_REFERER'];
            }
            elseif(isset($_GET['utm_referrer'])) {
                $this->utm['referrer'] = $_GET['utm_referrer'];
            }

            setcookie('nox_utm', base64_encode(json_encode($this->utm)), null, '/', noxSystem::$domain);

            $this->utm['ip'] = ip2long($_SERVER['REMOTE_ADDR']);
            $this->utm['url'] = noxSystem::$requestUrl;
            $this->insert($this->utm);
        }
    }

    public function hookUtm() {
        foreach(self::$params as $p) {
            if(isset($_GET[$p]) && $_GET[$p]) {
                $this->utm[$p] = $_GET[$p];
            }
        }

        $this->insertUtm();
    }
}