<?php
/**
 * noxAction
 *
 * Класс действия модуля
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage action
 */

class noxAction
{
    /**
     * Кэшировать ли результат работы действия. По-умолчанию кэшируется
     * @var bool
     */
    public $cache = true;

    /**
     * Время в секундах, на которое кэшируется результат действия. По-умолчанию кэширование на 10 минут
     * @var int
     */
    public $cacheTime = 600;

    /**
     * Название модуля
     * @var string
     */
    protected $moduleName;

    /**
     * Название раздела действия
     * @var string
     */
    protected $section;

    /**
     * Название действия
     * @var string
     */
    protected $action;

    /**
     * Папка текущего модуля
     * @var string
     */
    protected $moduleFolder;

    /**
     * Конструктор класса задает начальные параметры и создает переменные
     */
    public function __construct()
    {
        $this->params = noxSystem::$params;
        //Определяем имя модуля

        //Одиночное действие
        if (preg_match('/^([a-z0-9]*)([A-Z][^A-Z]+)(.*?)Action$/s', get_class($this), $matches))
        {
            $this->moduleName = $matches[1];
            $this->section = strtolower((!empty($matches[2])) ? $matches[2] : 'default');
            $this->action = (!empty($matches[3])) ? $matches[3] : '';
        }
        //Множественное действие
        elseif (preg_match('/^([a-z0-9]*)([A-Z][^A-Z]+)Actions$/s', get_class($this), $matches))
        {
            $this->moduleName = $matches[1];
            $this->section = strtolower((!empty($matches[2])) ? $matches[2] : '');
            $this->action = 'default';
        }
        $this->moduleFolder = 'nox-modules/' . $this->moduleName;
        $this->response = '';

        //POST запросы не кешируются
        if ($_SERVER['REQUEST_METHOD'] == 'POST')
        {
            $this->cache = false;
        }
    }

    /**
     * Загружает данные из кеша
     *
     * Если данных нет, то возвращает false
     * @return string
     */
    protected function loadFromCache()
    {
        if ($this->cache)
        {
            //Формируем имя для кеша: URL с параметрами
            if (isset($this->params['block']) && $this->params['block']==true)
            {
                $cacheName = __CLASS__ . $this->action . serialize($this->params);
            } else
            {
                $cacheName = __CLASS__ . $this->action . noxSystem::$fullUrl;
            }

            //Время изменения данного файла
            $time = filemtime(__FILE__);
            $t = time() - $this->cacheTime;
            if ($time < $t)
            {
                $time = $t;
            }

            //Получаем данные из кэша
            if ($cache = noxCache::get($cacheName, $time, true))
            {
                if (is_array($cache))
                {
                    if (isset($this->vars))
                    {
                        $this->vars = $cache['vars'];
                    }
                    return $cache['response'];
                }
            }
        }
        return false;
    }

    /**
     * Сохраняет $response в кеш действия
     *
     * @param $response
     * @return bool
     */
    protected function saveToCache($response)
    {
        //Сохраняем результат в кэш
        if ($this->cache)
        {
            //Формируем имя для кеша: URL с параметрами
            if (isset($this->params['block']) && $this->params['block']==true)
            {
                $cacheName = __CLASS__ . $this->action . serialize($this->params);
            } else
            {
                $cacheName = __CLASS__ . $this->action . noxSystem::$fullUrl;
            }
            

            $cache = array('response' => $response, 'vars' => array());
            if (isset($this->vars))
            {
                $cache['vars'] = $this->vars;
            }
            return noxCache::create($cacheName, $cache);
        }
        return false;
    }

    /**
     * Функция проверяет, существует ли закэшированный результат и, если
     * нет, то выполняет действие, иначе просто загружает данные из кэша
     *
     * @return int код ошибки
     */
    public function run()
    {
        //1)Проверяем кеш
        $response = $this->loadFromCache();
        if ($response !== false)
        {
            echo $response;
            return 200;
        }

        //2) Если данных нет, выполняем действия

        //Начинаем буферизацию
        ob_start();
        $code = $this->execute();
        $response = ob_get_contents();
        ob_end_clean();

        if (!$code) $code = 200;

        if ($code == 200)
        {
            $this->saveToCache($response);
            echo $response;
        }
        return $code;
    }

    /**
     * Основная рабочая функция. При ошибке возращает числовой код ошибки,
     * при успешой работе не возвращает ничего, либо true
     *
     * @return int код ошибки
     */
    public function execute()
    {

    }

    /**
     * Проверяет есть ли данное право у пользователя
     * @param string $right Право
     * @return bool
     */
    public function haveRight($right)
    {
        return noxSystem::$userControl->haveRight($this->moduleName, $right);
    }

    /**
     * Возвращает параметр по имени с приведением к типу и значению по-умолчанию
     * @param string $name   Имя параметра
     * @param mixed $default Значение по-умолчанию
     * @return mixed
     */
    public function getParam($name, $default = 0)
    {
        return getParam(@$this->params[$name], $default);
    }

    /**
     * Текущий запрос является ajax запросом?
     * @return bool
     */
    public function ajax()
    {
        return @$this->params['ajax'];
    }
}

?>