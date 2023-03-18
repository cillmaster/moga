<?php

class kafMedia
{
    /**
     * Прогрузка медиа из указанного источника
     *
     * @return string
     */
    public static function src($url){
        return noxConfig::getConfig()['mediaSrc'] . $url;
    }

    public static function srcMini($url){
        $url = str_replace(
            ['/preview/', '.png'],
            ['/mini-preview/','-blueprint-preview208.png'],
            $url
        );
        return noxConfig::getConfig()['mediaSrc'] . $url;
    }
}