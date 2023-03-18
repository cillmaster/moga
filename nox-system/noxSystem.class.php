<?php

/**
 * noxSystem
 *
 * Ядро NOX.CMS
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage core
 */
class noxSystem
{

    public static $version = '0.8.01.26';
    public static $prepayMaxDays = 5;
    /**
     * Объект управления загрузкой файлов
     * @var noxAutoLoader
     */
    public static $autoLoader = false;

    /**
     * Объект управления пользователями
     * @var noxUserControl
     */
    public static $userControl = false;

    /**
     * Объекты работы с корзиной
     * @var paymentCartModel
     */
    public static $cart;
    public static $cartItems;

    /**
     * Полный адрес с доменом
     *
     * @var string
     */
    public static $fullUrl = '/';

    /**
     * Домен запрашиваемого сайта без www
     *
     * @var string
     */
    public static $domain = '';

    /**
     * Весь URL запрос с параметрами
     *
     * @var string
     */
    public static $requestUrl = '/';

    /**
     * Путь к файлу/папке из URL без параметров
     *
     * @var string
     */
    public static $requestPath = '/';

    /**
     * Адрес, где расположен движок
     *
     * @var string
     */
    public static $baseUrl = '';

    /**
     * Адрес модуля из главного маршрутизатора
     *
     * @var string
     */
    public static $moduleUrl = '/';

    /**
     * Корневая папка модуля
     *
     * @var string
     */
    public static $moduleFolder = '/';

    /**
     * Адрес действия во внутреннем маршрутизаторе
     *
     * @var string
     */
    public static $actionUrl = '/';

    /**
     * Массив параметров
     *
     * @var array
     */
    public static $params = array();

    /**
     * Массив элементов строки запроса
     *
     * @var string
     */
    public static $urlArray = array();

    /**
     * Ajax запрос?
     *
     * @var bool
     */
    public static $ajax = false;

    /**
     * Безопасный запрос
     *
     * Если пользователь пришел с этого же сайта, то безопасный
     *
     * @var bool
     */
    public static $safeReferer = false;

    /**
     * Имя темы для вывода
     * @var string
     */
    public static $theme = '';

    /**
     * Папка темы для вывода
     * @var string
     */
    public static $themeFolder = '';

    /**
     * @var noxApplication
     */
    public static $application;

    /**
     * @var kafConsole
     */
    public static $console;

    /**
     * @var kafMedia
     */
    public static $media;

    /**
     * Переводит адрес в массив
     *
     * @param string $url адрес
     * @return array
     */
    public static function parseUrl($url)
    {
        //Декодируем URL -> раскладываем на массив ->
        //фильтруем пустые элементы -> преобразуем индексы
        return array_values(explode('/', trim(urldecode($url), '/')));
    }

    /**
     * Преобразовывает массив в URL
     *
     * @param array $array массив
     * @param int $start   номер начального элемента
     * @param int $count   количество элементов
     * @return string строка
     */
    public static function buildUrl($array = false, $start = null, $count = null)
    {
        //Если масссив не задан
        if ($array === false)
        {
            //Берем текущий массив URL
            $array = self::$urlArray;
        }
        //Обрезаем массив
        $t = array_slice($array, $start, $count);
        //Преобразуем в строку
        if (!$t)
        {
            return '/';
        }
        else
        {
            return '/' . implode('/', $t);
        }
    }

    /**
     * Запускает весь процесс работы движка
     */
    public static function run()
    {
        //Начинаем отчет времени
        $GLOBALS['statistic']['time'] = microtime(true);
        $GLOBALS['statistic']['dbQueries'] = 0;

        //Устанавливаем настройки PHP
        //Отображаем все ошибки
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 0);
        ini_set('error_log', 'perrorlog');
        set_error_handler('nox_error_handler');
        //Сессии
        ini_set('session.use_cookies', 0);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_trans_sid', 0);
        //Настройки
        ini_set('magic_quotes_runtime', 0);
        ini_set('magic_quotes_sybase', 0);
        ini_set('magic_quotes_gpc', 0);
        ini_set('allow_url_include', 0);

        //Настройка кодировки
        mb_internal_encoding('UTF-8');
        mb_http_output('UTF-8');

        //Стандартные заголовки
        header("Content-type: text/html; charset=utf-8", true);

        try
        {
            //Загружаем стандартное исключение
            require_once(noxRealPath('nox-system/exception/noxException.class.php'));
            //Загружаем класс кэширования
            require_once(noxRealPath('nox-system/cache/noxSystemCache.class.php'));
            //Загружаем автозагрузчик классов
            require_once(noxRealPath('nox-system/autoload/noxAutoLoader.class.php'));
            //Загружаем класс работы с датами
            require_once(noxRealPath('nox-system/date/noxDate.class.php'));

            //Загружаем класс работы с "консолью"
            require_once(noxRealPath('nox-system/console/kafConsole.class.php'));
            self::$console = new kafConsole();

            //Загружаем класс работы с media
            require_once(noxRealPath('nox-system/output/kafMedia.class.php'));
            self::$media = new kafMedia();

            //Автозагрузчик
            self::$autoLoader = new noxAutoLoader();

            //Загружаем класс отправки шаблонных email
            require_once(noxRealPath('nox-system/mail/kafMailer.class.php'));
            require_once(noxRealPath('nox-system/mail/postmarkMailer.class.php'));

            //Загружаем класс работы с постраничной навигацией
            require_once(noxRealPath('nox-system/output/kafPager.class.php'));

            //Константы
            include noxRealPath('nox-config/' . (noxConfig::isProduction() ? 'production' : 'dev') . '.env.php');

            //Загружаем настройки дат
            noxDate::updateConfig();

            //Задаем локаль по-умолчанию
            $config = noxConfig::getConfig();
            noxLocale::setLocale(isset($_COOKIE['nox_locale']) ? $_COOKIE['nox_locale'] : $config['defaultLocale']);
			//Если не режим отладки
			
            //Создаем объект управления пользователями
            self::$userControl = new noxUserControl();

            require_once(noxRealPath('nox-modules/payment/lib/models/paymentCart.model.php'));
            self::$cart = new paymentCartModel();
            self::$cartItems = self::$cart->getCartDetails();

            /****************************
             * Чтение URL
             ****************************/
            //Читаем домен
            self::$domain = $config['host']; //$_SERVER['SERVER_NAME'];

            //Base URL
            //TODO: Как определяется baseURL
            self::$baseUrl = trim(dirname($_SERVER['SCRIPT_NAME']), '\\/');
            if (empty(self::$baseUrl))
            {
                self::$baseUrl = '';
            } else
            {
                self::$baseUrl = '/'.self::$baseUrl;
            }

            //Берем URL с параметрами

            //Если есть параметр url берем с него
            self::$requestUrl = self::$requestPath = rtrim(urldecode($_SERVER['REQUEST_URI']), '/');
            self::$requestUrl = self::$requestPath = substr(self::$requestUrl, strlen(self::$baseUrl));
            if (empty(self::$requestUrl))
            {
                self::$requestUrl = self::$requestPath = '/';
            }

            //Полный адрес
            self::$fullUrl =
                ((empty($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] == 'off')) ? 'http://' : 'https://') .
                    self::$domain .
                    self::$baseUrl.
                    self::$requestUrl;

            //Получаем URL без параметров
            $i = strpos(self::$requestPath, '?');
            if ($i > 0)
            {
                self::$requestPath = '/'.trim(substr(self::$requestPath, 0, $i), '/');
            }
            //Массив URL
            self::$urlArray = self::parseUrl(self::$requestPath);

            function init_get_values($value, $key){
                $prm = explode(' ',$key);
                if(isset($prm[1]) && !isset($_GET[$prm[0]][$prm[1]]))
                    $_GET[$prm[0]][$prm[1]] = $value;
                else if(!isset($_GET[$prm[0]]))
                    $_GET[$prm[0]] = $value;
            }
            self::$params['get_preset'] = include(noxRealPath('nox-config/get.php'));
            array_walk_recursive(self::$params['get_preset'], 'init_get_values');

            //Защита от magic quotes
            if (@get_magic_quotes_gpc()) {
                function stripslashes_gpc(&$value)
                {
                    $value = stripslashes($value);
                }
                array_walk_recursive($_GET, 'stripslashes_gpc');
                array_walk_recursive($_POST, 'stripslashes_gpc');
                array_walk_recursive($_COOKIE, 'stripslashes_gpc');
                array_walk_recursive($_REQUEST, 'stripslashes_gpc');
            }

            //Сохраняем GET и POST массивы в параметры
            self::$params['get'] = $_GET;
            self::$params['post'] = $_POST;
            self::$params['requestMethod'] = $_SERVER['REQUEST_METHOD'];

            //Ajax запрос?
            self::$ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'))
                || isset($_REQUEST['ajax']);

            self::$params['ajax'] = self::$ajax;

            //Проверяем откуда пришел пользователь
            if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']))
            {
                $ref = $_SERVER['HTTP_REFERER'];
                $server_add =
                    ((empty($_SERVER['HTTPS']) || ($_SERVER['HTTPS'] == 'off')) ? 'http://' : 'https://').self::$domain;
                self::$safeReferer = !(bool)(substr_compare($ref, $server_add, 0, strlen($server_add)));
                self::$params['safeReferer'] = self::$safeReferer;
            }

            //Создаем приложение

            $userApplicationPath = noxRealPath('nox-config/application.class.php');
            if (file_exists($userApplicationPath))
            {
                include(noxRealPath('nox-config/application.class.php'));
                if (class_exists('application', false))
                {
                    $application = new application();
                } else
                {
                    $application = new noxApplication();
                }
            } else
            {
                $application = new noxApplication();
            }
            //Запускаем
            noxSystem::$application = $application;
            $application->run();

            //self::$route['statusCode'] = $statusCode;
            //_d(self:: getUser());

            //_d(self::dump());

            //Закрываем все открытые соединения с БД
            noxDbConnector::closeAll();

        } catch (Exception $e)
        {
            header("Content-type: text/html; charset=utf-8", true);
            print $e;
        }
        //_d(noxSystem::dump());
        //_d($_SERVER);
        $GLOBALS['statistic']['time'] = microtime(true)-$GLOBALS['statistic']['time'];
        //_d($GLOBALS['statistic']);
    }

    /**
     * Выполняет немедленный переход по адресу и прекращает выполнение скриптов
     *
     * @param string $url адрес для перехода
     * @param string $str сообщение о перенаправлении
     */
    public static function location($url = '', $str = "Перенаправление...")
    {
        if ($url == '')
        {
            $url = self::$fullUrl;
        } elseif (is_array($url))
        {
            $url = self::$baseUrl . '/' . implode('/', $url);
        } else
        {
            if ($url[0] == '/')
            {
                $url = self::$baseUrl . $url;
            }
        }
        header("Location: " . $url);
        exit($str);
    }

    /**
     * Выполняет немедленный переход  на предыдущую страницу и прекращает выполнение скриптов
     *
     * @param string $str сообщение о перенаправлении
     */
    public static function locationBack($str = "Перенаправление...")
    {
        $url = self::$fullUrl;
        if (empty($_SERVER['HTTP_REFERER']) or ($_SERVER['HTTP_REFERER'] == ($url)))
        {
            self::location('/', $str);
        } else
        {
            self::location($_SERVER['HTTP_REFERER'], $str);
        }
    }

    /**
     * Переход по адресу через указанное кол-во времени
     *
     * @param string $url адрес для перехода
     * @param int $time   время в секундах, спустя которое произойдет перенаправление
     * @param string $str сообщение о перенаправлении
     */
    public static function locationAfterTime($url = '', $time = 5, $str = '')
    {
        if ($url == '')
        {
            $url = self::$fullUrl;
        } elseif (is_array($url))
        {
            $url = self::$baseUrl . '/' . implode('/', $url);
        } else
        {
            if ($url[0] == '/')
            {
                $url = self::$baseUrl . $url;
            }
        }
        header("Refresh:{$time}; url=" . $url);
        if ($str)
        {
            exit($str);
        }
    }

    /**
     * Авторизует пользователя
     * @static
     * @return bool Результат авторизации
     */
    public static function authorization()
    {
        return self::$userControl->authorization();
    }

    /**
     * Возвращает модель пользователя
     * @static
     * @return noxUserModel
     */
    public static function getUserModel()
    {
        return self::$userControl->getUserModel();
    }

    /**
     * Возвращает ID темущего пользователя
     *
     * @static
     * @return int
     */
    public static function getUserId()
    {
        return self::$userControl->getUserId();
    }

    /**
     * Возвращает массив с данными текущего пользователя
     * @static
     * @return array
     */
    public static function getUser()
    {
        return self::$userControl->getUser();
    }

    /**
     * Проверяет, является ли пользователь членом группы
     *
     * @param $group_id
     * @param int $user_id 0 для текущего пользователя
     * @return bool
     */
    public static function userInGroup($group_id, $user_id=0)
    {
        return self::$userControl->userInGroup($group_id, $user_id);
    }

    /**
     * Проверяет, имеет ли пользователь право
     *
     * @param string $module модуль, которому необходимо право
     * @param string $right  идентификатор права
     * @return bool
     */
    public static function haveRight($module, $right)
    {
        return self::$userControl->haveRight($module, $right);
    }

    /**
     * Возвращает дамп переменных системы (в основном маршрутизация)
     *
     * @static
     * @return string
     */
    public static function dump()
    {
        return
            'Full URL: ' . htmlspecialchars(self::$fullUrl) . "\n"
            . 'Base URL: ' . htmlspecialchars(self::$baseUrl) . "\n"
            . 'Request URL: ' . htmlspecialchars(self::$requestUrl) . "\n"
            . 'Request Path: ' . htmlspecialchars(self::$requestPath) . "\n"
            . 'Module URL: ' . htmlspecialchars(self::$moduleUrl) . "\n"
            . 'Action URL: ' . htmlspecialchars(self::$actionUrl) . "\n"
            . 'Params: ' . _d(self::$params, true) . "\n"
            . 'Module Folder: ' . htmlspecialchars(self::$moduleFolder) . "\n";
    }

    /**
     * Возвращает массив со статистикой движка
     *
     * @static
     * @return mixed
     */
    public static function getStatistic()
    {
        //Заканчиваем отчет времени
        $GLOBALS['statistic']['time'] = microtime(true) - @$GLOBALS['statistic']['time'];
        //Вывод статистики
        //_d($GLOBALS['statistic']);
        return $GLOBALS['statistic'];
    }

}

/*
Дополнительные функции
*/

/**
 * Выводит или возвращает значение переменной в теге <pre>
 *
 * @param mixed $var   переменная
 * @param bool $return возвращать или выводить результат
 * @return string
 */
function _d($var, $return = false)
{
    $text = print_r($var, true);
    if (!$return)
    {
        $text = htmlspecialchars($text);
    }
    if (!$return)
    {
        echo '<pre class="debug">' . $text . '</pre>';
    }
    return $text;
}

/**
 * Форматирует размер файла
 *
 * @param int $bytes размер в байтах
 * @return string
 */
function noxFormatBytes($bytes)
{
    if ($bytes < 1024)
    {
        return $bytes . ' B';
    }
    elseif ($bytes < 1048576)
    {
        return round($bytes / 1024, 2) . ' KB';
    }
    elseif ($bytes < 1073741824)
    {
        return round($bytes / 1048576, 2) . ' MB';
    }
    elseif ($bytes < 1099511627776)
    {
        return round($bytes / 1073741824, 2) . ' GB';
    }
    else
    {
        return round($bytes / 1099511627776, 2) . ' TB';
    }
}

/**
 * Возвращает параметр $var с приведением к типу $default и его значением
 * @param mixed $var     Параметр
 * @param mixed $default Значение по-умолчанию
 * @return mixed
 */
function getParam($var, $default = 0)
{
    settype($var, gettype($default));
    if (!$var)
    {
        return $default;
    } else
    {
        return $var;
    }
}

/**
 * Преобразует относительный путь от корня сайта в абсолютный
 * @param $path
 * @return string
 */
function noxRealPath($path)
{
    static $realpath = '';
    if (!$realpath)
    {
        $realpath = str_replace('\\', '/', realpath(dirname(__FILE__) . '/..')) . '/';
    }

    $path = str_replace('\\', '/', trim($path, '\//'));
    return $realpath . $path;
}

function nox_error_handler($errno, $errstr, $file, $line) {
return;
    $trace = debug_backtrace();
    $report = ['=== START ==='];
    $report[] = "Url: {$_SERVER['REQUEST_URI']};";
    $report[] = "Method: {$_SERVER['REQUEST_METHOD']};";
    $report[] = "Referer: {$_SERVER['HTTP_REFERER']};";
    $report[] = "-----------------------------------";
    $report[] = "ERRNO: $errno;";
    $report[] = "ERRSTR: $errstr;";
    $report[] = "FILE: {$file}:{$line}";

    for($i = 1 , $l = count($trace); $i < $l; $i++)
    {
        if(!noxConfig::isProduction())
            echo "FILE: {$file}:{$line} </br>";
        $item = $trace[$i];
        $log = $l - $i . ': ';
        if(isset($item['file'])) {
            $log .= $item['file'] . ':' . $item['line'];
        }
        if(isset($item['function'])) {
            $fn = $item['function'] . '(' . implode(', ', array_map('nox_error_handler_args_maker', $item['args'])) . ')';
            if(isset($item['type']) && isset($item['object'])) {
                $fn = get_class($item['object']) . $item['type'] . $fn;
            }
            $log .= ' ' . $fn;
        }
        $report[] = $log;
    }
    $report[] = '';

    $report[] = print_r(getallheaders(), true);
    $report[] = '=== END ===';

    error_log(implode("\n", $report));
}

function nox_error_handler_args_maker($x) {
    $type = gettype($x);
    switch($type) {
        case 'string':
            return "'{$x}'";
        case 'boolean':
        case 'integer':
        case 'double':
        case '':
            return $x;
        default:
            return "'[{$type}]'";
    }
}

function gen($n){
    $base = []; $cont = []; $out = '';
    $n = $n - strlen($out);
    for($i = 48; $i <= 57; $i++) $base[] = $i;
    for($i = 65; $i <= 90; $i++) $base[] = $i;
    for($i = 97; $i <= 122; $i++) $base[] = $i;
    for($i = 0; $i < rand(50, 100); $i++) $cont[] = $base[array_rand($base)];
    for($i = 0; $i < $n; $i++) $out .= chr($cont[array_rand($cont)]);
    return $out;
}
