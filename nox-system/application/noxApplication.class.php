<?php

/**
 * Приложение
 *
 * Выполняет всю работу по маршрутизации и запуску действий
 *
 * @version    1.2
 * @author Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 */

class noxApplication
{
    /**
     * Главный маршрут
     *
     * @var array
     */
    public $routes;

    /**
     * Имя модуля
     * @var string
     */
    public $moduleName;

    /**
     * Раздел модуля
     * @var string
     */
    public $sectionName;

    /**
     * Имя действия
     * @var string
     */
    public $actionName;


    /**
     * Определяет главный маршрут
     */
    public function runDomainRouting()
    {
        $this->routes = noxConfig::getDomainRoutes(noxSystem::$domain);
    }

    /**
     * Маршутизация до действия
     * @return int код
     */
    public function runRouting()
    {
        /****************************
         * Маршрутизатор
         ****************************/

        //Флаг найденного модуля
        $routeFound = false;

        //Имя модуля
        $moduleName = '';
        //Имя дейсвия
        $action = '';
        $section = '';

        //Загружаем информацию об установленных модулях
        $modules = noxConfig::getModules();

        //Выбираем маршрутизатор
        if (noxSystem::$urlArray[0] == 'administrator')
        {
            if (noxSystem::authorization())
            {
                //Срабатывает только с url и только с авторизацией

                /****************************
                 * Маршрутизатор для бекэнда
                 ****************************/

                noxSystem::$params['backend'] = true;
                noxSystem::$params['frontend'] = false;

                if (isset(noxSystem::$urlArray[1]))
                {
                    if(!isset(noxSystem::$params['get']['action'])) noxSystem::$params['get']['action'] = '';
                    $moduleName = getParam(@noxSystem::$urlArray[1], 'system');
                    $section = getParam(@noxSystem::$params['get']['section'], 'administrator');
                    $action = getParam(@noxSystem::$params['get']['action'], '');
                } else
                {
                    $moduleName = 'system';
                    $section = 'administrator';
                    $action = '';
                }

                //URL
                noxSystem::$actionUrl = '/';
                noxSystem::$moduleUrl = noxSystem::$requestPath;
                noxSystem::$moduleFolder = noxRealPath('nox-modules/' . $moduleName);

                $this->moduleName = $moduleName;
                $this->sectionName = ucfirst($section);
                $this->actionName = ucfirst($action);

                return 200;
            }
            else
            {
                //Нет прав
                return 401;
            }
        }
        else
        {
            /****************************
             * Маршрутизатор для фронтенда
             ****************************/

            noxSystem::$params['backend'] = false;
            noxSystem::$params['frontend'] = true;

            //Обрабатываем маршруты, чтобы определить модуль
            foreach ($this->routes as $route)
            {
                if (!$route['enabled'])
                {
                    continue;
                }

                $preg = $route['url'];

                if ($preg == '*')
                {
                    $preg = '/^(\/|(.*?))$/si';
                } else
                {
                    //1) Экранируем слеши и ост. символы
                    $preg = str_replace(array('*', '/'), array('(.*?)', '\/'), $preg);
                    //2) Заменяем последний параметр для возможных пустых значений
                    if (substr($preg, -7, 7) == '\/(.*?)')
                    {
                        $preg = substr($preg, 0, -7) . '(\/|(.*?))';
                    }
                    //3) Дополняем
                    $preg = '/^\/' . $preg . '$/si';
                }

                //Записываем все совпадения
                if (preg_match($preg, noxSystem::$requestPath, $matches))
                {
                    //URL действия

                    if (count($matches)==1)
                    {
                        noxSystem::$actionUrl = '/';
                        noxSystem::$moduleUrl = noxSystem::$requestPath;
                    } else
                    {
                        $lastElement = @$matches[count($matches)-1];

                        if (empty($lastElement))
                        {
                            noxSystem::$actionUrl = '/';
                            noxSystem::$moduleUrl = noxSystem::$requestPath;
                        } else
                        {
                            noxSystem::$actionUrl = $lastElement;
                            noxSystem::$moduleUrl = substr(noxSystem::$requestPath, 0, -strlen(noxSystem::$actionUrl));
                        }
                    }
                    //Ищем маршрут внутри модуля

                    //Имя модуля
                    $moduleName = $route['module'];

                    //Если модуль не установлен, то выходим
                    if (!isset($modules[$moduleName]))
                    {
                        continue;
                    }

                    noxSystem::$moduleFolder = noxRealPath('nox-modules/' . $route['module']);

                    //Файл внутреннего маршрутизатора
                    $routerPath = noxSystem::$moduleFolder . '/lib/config/routes.php';

                    //Проверяем, существует ли файл маршрутизации
                    if (!file_exists($routerPath))
                    {
                        //Если нет, то переходим к следующему маршруту
                        continue;
                    }

                    //Внутренняя маршрутизаци
                    $moduleRoutes = include($routerPath);
                    foreach ($moduleRoutes as $mRoute)
                    {
                        if (!@$mRoute['enabled'])
                        {
                            continue;
                        }

                        $preg = $mRoute['url'];

                        if (empty($preg))
                        {
                            $preg = '/^\/$/si';
                        } else
                        {
                            //1) Экранируем слеши и ост. символы
                            $preg = str_replace(array('*', '/'), array('(.*?)', '\/'), $preg);
                            //2) Создаем шаблоны для параметров
                            $preg = preg_replace('/\<([A-Za-z0-9_-]*?)\>/si', '(?P<$1>[^\/]+)', $preg);
                            $preg = preg_replace('/\<([A-Za-z0-9_-]*?)\:(.*?)\>/si', '(?P<$1>$2)', $preg);
                            //3) Дополняем
                            $preg = '/^\/' . $preg . '$/si';
                        }



                        //Записываем все совпадения
                        if (preg_match($preg, noxSystem::$actionUrl, $matches))
                        {
                            //Маршрут найден
                            $routeFound = $route['routeFound'] = true;

                            //Обрабатываем параметры
                            foreach ($matches as $k=> $v)
                            {
                                if (is_string($k))
                                {
                                    noxSystem::$params[$k] = $v;
                                }
                            }
                            //Действие модуля
                            $action = (isset($mRoute['action'])) ? $mRoute['action'] : '';
                            $section = (isset($mRoute['section'])) ? $mRoute['section'] : 'default';

                            break;
                        }
                    }
                    //Если маршут найден
                    if ($routeFound)
                    {
                        //Выходим из маршрутизатора
                        $this->moduleName = $moduleName;
                        $this->sectionName = ucfirst($section);
                        $this->actionName = ucfirst($action);
                        return 200;
                    }
                }
            }
        }
        return 404;
    }


    /**
     * Выполняет действие
     *
     * @return int код
     */
    public function runAction()
    {
        $initPath = noxRealPath('nox-modules/' . $this->moduleName . '/lib/config/init.php');
        //Проверяем, существует ли файл инициализации
        if (file_exists($initPath))
        {
                include_once($initPath);
        }

        //Проверям раздел и действие

        //Создаем класс контроллера вида administratorConfigController
        $className = $this->moduleName . $this->sectionName . $this->actionName . 'Controller';
        //Пробуем загрузить класс
        if (class_exists($className, true))
        {
            noxSystem::$console->log('/' . $className . '.action.php');
            //Работаем с контроллером

            //Создаем объект контроллера
            $class = new $className();
            //Выполняем дейсвтие
            $statusCode = $class->run();
            unset($class);
            return $statusCode;
        } else
        {
            //Создаем класс отдельного действия administratorConfigEditAction
            $className = $this->moduleName . $this->sectionName . $this->actionName . 'Action';

            //Пробуем загрузить класс
            if (class_exists($className, true))
            {
                noxSystem::$console->log('/' . $className . '.action.php');
                //Работаем с действием

                //Создаем объект действия
                $class = new $className();
                //Выполняем дейсвтие
                $statusCode = $class->run();
                unset($class);
                return $statusCode;
            } else
            {
                //Создаем класс множественного действия administratorConfigActions
                $className = $this->moduleName . $this->sectionName . 'Actions';
                if (empty($this->actionName)) $this->actionName = 'Default';

                //Пробуем загрузить класс и проверяем, есть ли в нем нужный метод
                if (class_exists($className, true) && method_exists($className, 'action' . $this->actionName))
                {
                    noxSystem::$console->log('/' . $className . '.action.php');
                    noxSystem::$console->log('action' . $this->actionName);
                    //Работаем со множественным действием

                    //Создаем объект действия
                    $class = new $className();
                    //Выполняем дейсвтие
                    $statusCode = $class->run($this->actionName);
                    unset($class);
                    return $statusCode;
                }
            }
        }
        return 404;
    }

    /**
     * Выполняет все действия по приложению
     */
    public function run()
    {
        noxSystem::$params['backend'] = false;
        noxSystem::$params['frontend'] = true;

        //Запускаем главный маршрутизатор
        $this->runDomainRouting();
        $statusCode = $this->runRouting();

        noxSystem::$console->log(noxSystem::$moduleFolder);
        noxSystem::$console->log('/lib/action/' . (noxSystem::$params['backend'] ? 'back' : 'front') . 'end');

        if ($statusCode == 200)
        {
            $statusCode = $this->runAction();
        }

        //Если ошибка или класс не найден
        if ($statusCode && ($statusCode != 200))
        {
            //Если ошибка числовая, вызываем дейсвие ошибки
            if (is_numeric($statusCode))
            {
                $class = new errorActions();
                $class->run($statusCode);
            } elseif (is_string($statusCode))
            {
                //Иначе, выводим строку исключением
                throw new noxException($statusCode);
            }
        }
    }
}
