<?php

class kafConsole
{
    /**
     * Массив параметров
     *
     * @var array
     */
    private static $params = array();

    /**
     * Сборщик служебных сообщений в режиме разработки
     *
     * @var string
     * @return void
     */
    public static function log($msg){
        if(noxConfig::getConfig()['is_console']){
            self::$params['console'][] = $msg;
        }
    }

    /**
     * Вывод служебных сообщений в режиме отладки
     *
     * @return string
     */
    public static function write(){
        $out = '<div id="console"><div>';
        $out .= join('</div><div>', self::$params['console']);
        return $out . '</div></div>';
    }
}