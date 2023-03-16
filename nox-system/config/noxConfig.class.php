<?php
/**
 * noxConfig
 *
 * Класс noxConfig, служащий для управления настройками сайта
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage core
 */

class noxConfig
{
    /**
     * Кэш
     * @var array
     */
    private static $cache = array();

    /**
     * Возвращает массив с настройками БД
     *
     * @return array
     */
    public static function getDb()
    {
        if (!isset(self::$cache['db']))
        {
            self::$cache['db'] = include(noxRealPath('nox-config/db.php'));
        }
        return self::$cache['db'];
    }

    /**
     * Возвращает массив с модулями
     *
     * @return array
     */
    public static function getModules()
    {
        if (!isset(self::$cache['modiles']))
        {
            self::$cache['modules'] = include(noxRealPath('nox-config/modules.php'));
        }
        return self::$cache['modules'];
    }

    /**
     * Возвращает массив со всем маршрутами
     *
     * @return array
     */
    public static function getRoutes()
    {
        if (!isset(self::$cache['routes']))
        {
            self::$cache['routes'] = include(noxRealPath('nox-config/routes.php'));
        }
        return self::$cache['routes'];
    }

    /**
     * Возвращает массив с маршрутами для определенного домена или маршрут по-умолчанию
     *
     * @param string $domain Имя домена без WWW
     * @return array
     */
    public static function getDomainRoutes($domain = 'default')
    {
        $ar = self::getRoutes();
        if (isset($ar[$domain]))
        {
            return $ar[$domain];
        } else
        {
            return @$ar['default'];
        }
    }

    /**
     * Возвращает массив с темами
     *
     * @return array
     */
    public static function getThemes()
    {
        if (!isset(self::$cache['themes']))
        {
            self::$cache['themes'] = include(noxRealPath('nox-config/themes.php'));
        }
        return self::$cache['themes'];
    }

    /**
     * Возвращает массив с блоками
     *
     * @return array
     */
    public static function getBlocks()
    {
        if (!isset(self::$cache['blocks']))
        {
            self::$cache['blocks'] = include(noxRealPath('nox-config/blocks.php'));
        }
        return self::$cache['blocks'];
    }

    /**
     * Возвращает массив с настройками
     *
     * @return array Массив с настройками
     */
    public static function getConfig()
    {
        if (!isset(self::$cache['config']))
        {
            self::$cache['config'] = include(noxRealPath('nox-config/config.php'));
        }
        return self::$cache['config'];
    }

    /**
     * Возвращает флаг режима отладки
     *
     * @return bool
     */
    public static function isDebug()
    {
        $config = self::getConfig();
        return @$config['debug'];
    }

    public static function isProduction()
    {
        return self::getConfig()['is_production'];
    }

    /**
     * Сохраняет массив в файл
     *
     * @param array  Массив
     * @param string Имя файла без папки (напр. config.php)
     * @return bool
     */
    public static function saveConfigToFile($array, $filename)
    {
        if (!$array)
        {
            return false;
        }
        if (!$filename)
        {
            return false;
        }
        $filename = noxRealPath('nox-config/' . $filename);
        if (!file_exists($filename))
        {
            return false;
        }

        //Если в файл нельзя записать
        if (!is_writable($filename))
        {
            chmod(dirname($filename), 0777);
            chmod($filename, 0644);
        }

        $data = "<?php \nreturn " . var_export($array, true) . "\n?>";
        return (file_put_contents($filename, $data) > 0);
    }

    /**
     * Сохраняет массив с настройками
     *
     * @param array Массив
     * @return bool
     */
    public static function saveConfig($array)
    {
        ksort($array);
        unset(self::$cache['config']);
        return self::saveConfigToFile($array, 'config.php');
    }

    /**
     * Сохраняет массив с темами
     *
     * @param array Массив
     * @return bool
     */
    public static function saveThemes($array)
    {
        ksort($array);
        unset(self::$cache['themes']);
        return self::saveConfigToFile($array, 'themes.php');
    }

    /**
     * Сохраняет массив с модулями
     *
     * @param array Массив
     * @return bool
     */
    public static function saveModules($array)
    {
        ksort($array);
        unset(self::$cache['modules']);
        return self::saveConfigToFile($array, 'modules.php');
    }

    /**
     * Сохраняет массив с бд
     *
     * @param array Массив
     * @return bool
     */
    public static function saveDb($array)
    {
        unset(self::$cache['db']);
        return self::saveConfigToFile($array, 'db.php');
    }

    /**
     * Сохраняет массив с блоками
     *
     * @param array Массив
     * @return bool
     */
    public static function saveBlocks($array)
    {
        unset(self::$cache['blocks']);
        return self::saveConfigToFile($array, 'blocks.php');
    }

    /**
     * Добавляет блок
     *
     * @param string имя блока
     * @param array  Массив
     * @return bool
     */
    public static function addBlock($name, $array)
    {
        $blocks = self::getBlocks();
        $blocks[$name] = $array;
        unset(self::$cache['blocks']);
        return self::saveConfigToFile($blocks, 'blocks.php');
    }

    /**
     * Удаляет блок
     *
     * @param string имя блока
     * @return bool
     */
    public static function deleteBlock($name)
    {
        $blocks = self::getBlocks();
        unset(self::$cache['blocks']);
        return self::saveConfigToFile($blocks, 'blocks.php');
    }

    /**
     * Сохраняет массив с маршрутами
     *
     * @param array Массив
     * @return bool
     */
    public static function saveRoutes($array)
    {
        unset(self::$cache['routes']);
        return self::saveConfigToFile($array, 'routes.php');
    }
}

?>