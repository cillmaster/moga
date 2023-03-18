<?php
/**
 * noxGeo
 *
 * Класс для работы с geo ip
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage geo
 */

class noxGeo
{
    public static $object = false;

    /**
     * Объект SxGeo
     * @var SxGeo
     */
    private $sxGeo;

    public function __construct()
    {
        require_once(dirname(__FILE__) . '/sxgeo/SxGeo.php');
        $this->sxGeo = new SxGeo(dirname(__FILE__) . '/sxgeo/SxGeoCity.dat', SXGEO_BATCH | SXGEO_MEMORY);
    }

    public function __destruct()
    {
        unset($this->sxGeo);
        self::$object = false;
    }

    /**
     * Возвращает экземпляр класса
     *
     * @return noxGeo
     */
    public static function getInstance()
    {
        if (!self::$object)
        {
            self::$object = new self();
        }
        return self::$object;
    }

    /**
     * Возвращает код страны по IP
     *
     * @param string IP
     * @return string
     */
    public static function getCountry($ip=false)
    {
        if (!$ip)
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $city = self::getInstance()->sxGeo->getCity($ip);
        return $city['country'];
    }

    /**
     * Возвращает информацию о городе по IP
     *
     * @param string IP
     * @return string
     */
    public static function getCity($ip=false)
    {
        if (!$ip)
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return self::getInstance()->sxGeo->getCityFull($ip);
    }
}

?>