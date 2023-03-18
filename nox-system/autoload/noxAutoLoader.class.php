<?php
/**
 * noxAutoLoader
 *
 * Класс для загрузки классов из файлов
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage core
 */

class PsrAutoloader {

    /**
     * @var array of arrays of strings
     */
    private $hosts = [];
    private $ext = '.php';

    public function __construct()
    {
        if (spl_autoload_register(array($this, 'autoload')) === false) {
            throw new noxException(sprintf('Невозможно зарегистрировать %s::autoload как метод автозагрузки!', get_class($this)));
        }
    }

    public function addHost($start, $path)
    {
        $this->hosts[] = [$start, $path];
    }

    public function autoload($clazz) {
        foreach($this->hosts as $host) {
            if($host[0] === substr($clazz, 0, strlen($host[0]))) {
                $clazz = str_replace($host[0], $host[1], $clazz);
                break;
            }
        }

        $clazz .= $this->ext;
        $clazz = noxRealPath($clazz);

        return file_exists($clazz) && require_once $clazz;
    }
}

class noxAutoLoader
{
    private $cacheChange = false;

    public function __construct()
    {
        //Регистрируем функцию автозагрузки
        ini_set('unserialize_callback_func', 'spl_autoload_call');
        if (spl_autoload_register(array($this, 'autoload')) === false) {
            throw new noxException(sprintf('Невозможно зарегистрировать %s::autoload как метод автозагрузки!', get_class($this)));
        }

        $psrAutoloader = new PsrAutoloader();
        $psrAutoloader->addHost('nox', 'nox-system');

        //Пробуем получить данные из кеша
        if ($cache = noxSystemCache::get('nox-autoloader')) {
            $this->classes = array_merge($cache, $this->classes);
        }
    }

    /**
     * Деструктор. Сохраняем кеш
     */
    public function __destruct()
    {
        if ($this->cacheChange) {
            noxSystemCache::create('nox-autoloader', $this->classes);
        }
    }

    /**
     * Добавляет путь к классу в известные пути
     *
     * @param $class Либо имя класса, либо массив 'класс'=>'путь'.
     * @param $path  Путь к файлу, либо ничего.
     * @return $this
     */
    public function add($class, $path = '')
    {
        if (is_array($class)) {
            foreach ($class as $class_name => $path) {
                $this->classes[strtolower($class_name)] = $path;
            }
        } else {
            $this->classes[strtolower($class)] = $path;
        }
        $this->cacheChange = true;
        return $this;
    }

    /**
     * Находит путь к файлу, содержащему класс
     *
     * @param string $class Имя класса
     * @return string
     */
    public function get3rdparty($class) {
        //_d($class);
        $lowClass = strtolower($class);
        //Maybe PayPal?
        static $libFolder = '/nox-modules/3rdparty/';
        $className = noxRealPath($libFolder . $class . '.php');

        if(file_exists($className)) {
            $this->classes[ $lowClass ] = $className;
            $this->cacheChange = true;
            return $className;
        }
        else {
            return false;
        }
    }

    public function get($class)
    {
        //Преобразуем имя класса в нижний регистр
        $lowclass = strtolower($class);

	    if (isset($this->classes[$lowclass])) {
		    if (file_exists($this->classes[$lowclass])) {
			    return $this->classes[$lowclass];
		    } else
		    {
			    unset($this->classes[$lowclass]);
			    $this->cacheChange = true;
		    }
	    }

        if (!isset(noxSystem::$params['frontend']))
        {
            return false;
        }

        $path = '';

        //Это модель?
        if (substr($class, -5, 5) == 'Model')
        {
            if (preg_match('/^([a-z0-9]*)(.*?)Model$/s', $class, $matches)) {
                $path = 'nox-modules/' . $matches[1] . '/lib/models/' . $matches[1] . $matches[2] . '.model.php';
            } else
            {
                return $this->get3rdparty($class);
            }

        }
        else
        {
	        $lowclass = ( noxSystem::$params['frontend'] ? 'frontend/' :
			        (noxSystem::$params['backend'] ? 'backend/' : '') ).$lowclass;

	        if (isset($this->classes[$lowclass])) {
		        if (file_exists($this->classes[$lowclass])) {
			        return $this->classes[$lowclass];
		        } else
		        {
			        unset($this->classes[$lowclass]);
			        $this->cacheChange = true;
		        }
	        }

            $filename = '';
            $module = '';

            if (substr($class, -7, 7) == 'Actions')
            {

                if (preg_match('/^([a-z0-9]*)Actions$/s', $class, $matches)) {
                    $filename = $matches[1] . '.actions.php';
                    $module = $matches[1];
                }
                //Если это действие
                elseif (preg_match('/^([a-z0-9]*)([A-Z][^A-Z]*)(.*?)Actions$/s', $class, $matches)) {
                    $filename = $matches[1] . $matches[2] . $matches[3] . '.actions.php';
                    $module = $matches[1];
                }

            } elseif (substr($class, -6, 6) == 'Action')
            {

                if (preg_match('/^([a-z0-9]*)([A-Z][^A-Z]*)(.*?)Action$/s', $class, $matches)) {
                    $filename = $matches[1] . $matches[2] . $matches[3] . '.action.php';
                    $module = $matches[1];
                }
            }

            if (!$filename)
            {
                return $this->get3rdparty($class);
            }

            $path = 'nox-modules/' . $module . '/lib/actions/' .
                (noxSystem::$params['frontend'] ? 'frontend/' :
                    (noxSystem::$params['backend'] ? 'backend/' : '') )
                . $filename;
            if (!file_exists($path))
            {
                $path = 'nox-modules/' . $module . '/lib/actions/' . $filename;
            }
        }

        if (file_exists($path)) {
            $this->classes[ $lowclass ] = $path;
            $this->cacheChange = true;
            return $path;
        }
         else
        {
            return $this->get3rdparty($class);
        }
    }

    public function autoload($class)
    {
        //Получаем путь и загружаем файл
        if ($path = $this->get($class)) {
            require_once($path);
        }
        return true;
    }


    /**
     * Массив классов с известными путями к системным файлам
     *
     * @var array
     */
    protected $classes = array(
        //Ядро
        'noxconfig'                => 'nox-system/config/noxConfig.class.php',
        'noxlocale'                => 'nox-system/locale/noxLocale.class.php',
        'noxdate'                  => 'nox-system/date/noxDate.class.php',
        'noxcache'                 => 'nox-system/cache/noxCache.class.php',
        //Базы данных
        'noxdbadapter'             => 'nox-system/db/noxDbAdapter.class.php',
        'noxdbmysqladapter'        => 'nox-system/db/noxDbMySQLAdapter.class.php',
        'noxdbmysqliadapter'       => 'nox-system/db/noxDbMySQLiAdapter.class.php',
        'noxdbmsaccessadapter'     => 'nox-system/db/noxDbMsAccessAdapter.class.php',
        'noxdbconnector'           => 'nox-system/db/noxDbConnector.class.php',
        'noxdbquery'               => 'nox-system/db/noxDbQuery.class.php',
        'noxmodel'                 => 'nox-system/db/noxModel.class.php',
        //Модули
        'noxapplication'           => 'nox-system/application/noxApplication.class.php',
        'noxcontroller'            => 'nox-system/action/noxController.class.php',
        'noxaction'                => 'nox-system/action/noxAction.class.php',
        'noxactions'               => 'nox-system/action/noxActions.class.php',
        'noxjsonaction'            => 'nox-system/action/noxJsonAction.class.php',
        'noxtemplateaction'        => 'nox-system/action/noxTemplateAction.class.php',
        'noxthemeactions'          => 'nox-system/action/noxThemeActions.class.php',
        'noxthemeaction'           => 'nox-system/action/noxThemeAction.class.php',
        'noxrssaction'             => 'nox-system/action/noxRssAction.class.php',
        //Вывод
        'noxtemplate'              => 'nox-system/output/noxTemplate.class.php',
        'noxform'                  => 'nox-system/output/noxForm.class.php',
        'noxmail'                  => 'nox-system/mail/noxMail.class.php',
        'noxmodelform'                => 'nox-system/output/noxModelForm.class.php',
        'noxjson'                => 'nox-system/output/noxJson.class.php',

        //Пользователи
        'noxusercontrol'           => 'nox-system/user/noxUserControl.class.php',
        'noxusermodel'             => 'nox-system/user/noxUser.model.php',
        'noxgroupmodel'            => 'nox-system/user/noxGroup.model.php',
        'noxgrouprightsmodel'      => 'nox-system/user/noxGroupRights.model.php',
        'noxusergroupsmodel'       => 'nox-system/user/noxUserGroups.model.php',
        'noxsessionmodel'          => 'nox-system/user/noxSession.model.php',

        //Файловая система
        'noxfilesystem'            => 'nox-system/file/noxFileSystem.class.php',

        //GeoIP
        'noxgeo'                   => 'nox-system/geo/noxGeo.class.php'
    );
}

?>
