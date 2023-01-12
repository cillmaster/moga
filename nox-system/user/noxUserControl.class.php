<?php
/**
 * noxUserControl
 *
 * Класс системы авторизации и прав пользователей, хранящихся в БД
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage user
 */

class noxUserControl
{
    /**
     * Авторизация прошла
     * @var bool
     */
    private $auth_checked = false;

    /**
     * Пользователь авторизован
     * @var bool
     */
    private $auth = false;

    /**
     * ID авторизованного пользователя
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * Имя авторизованного пользователя
     * @var string
     */
    private $user_name = '';

    /**
     * Имя БД для хранения пользователей
     * @var string
     */
    public $db;

    /**
     * Модель пользователя
     * @var noxModel
     */
    public $userModel;

    /**
     * Модель сессии
     * @var noxSessionModel
     */
    public $sessionModel;

    /**
     * Массив с профилем пользователя
     * @var array
     */
    public $user;

    /**
     * Модель прав
     * @var noxModel
     */
    public $rightModel;

    /**
     * Конструктор
     */
    public function __construct()
    {
        $config = noxConfig::getConfig();
        //Пользователей из БД пользователей
        $this->db = @$config['userDb'];

        $this->userModel = new noxUserModel($this->db);
        $this->rightModel = new noxGroupRightsModel();
        $this->sessionModel = new noxSessionModel();
    }

    /**
     * Деструктор
     */
    public function __destruct()
    {
        unset($this->sessionModel);
        unset($this->userModel);
        unset($this->rightModel);
    }

    /**
     * Возвращает модель пользователей
     *
     * @return noxUserModel
     */
    public function getUserModel()
    {
        return new noxUserModel($this->db);
    }

    /**
     * Возвращает идентификатор сессии
     * @param $new Требуется создать новый ID
     * @return string
     */
    public function getSessionId($new=false)
    {
        if (!$new)
        {
            if (isset($_COOKIE['nox_sid']))
            {
                return $_COOKIE['nox_sid'];
            } else
            {
                return false;
            }
        }
        if ($new)
        {
            return noxSessionModel::hash(time().$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT']);
        }
    }

    /**
     * Добавляет переменную в сессию
     * @param $name
     * @param $value
     * @return bool
     */
    public function writeToSession($name, $value)
    {
        if ($id = $this->getSessionId())
        {
            //Записывам сессию
            if ($data = $this->sessionModel->getData($id))
            {
                $data[$name] = $value;
                return $this->sessionModel->saveData($id, $data);
            }
        }
        return false;
    }

    /**
     * Возвращает переменную из сессии
     * @param $name
     * @return mixed
     */
    public function getFromSession($name)
    {
        if ($id = $this->getSessionId())
        {
            //Записывам сессию
            if ($data = $this->sessionModel->getData($id))
            {
                if (isset($data[$name]))
                {
                    return $data[$name];
                }

            }
        }
        return false;
    }

    /**
     * Удаляет переменную из сессии
     * @param $name
     * @return bool
     */
    public function removeFromSession($name)
    {
        if ($id = $this->getSessionId())
        {
            //Записывам сессию
            if ($data = $this->sessionModel->getData($id))
            {
                if (isset($data[$name]))
                {
                    unset($data[$name]);
                }
                return $this->sessionModel->saveData($id, $data);
            }
        }
        return false;
    }

    /**
     * Проверка авторизации пользователя
     *
     * @return bool результат проверки
     */
    public function authorization()
    {
        if ($this->auth_checked)
        {
            return $this->auth;
        }

        $GLOBALS['vars']['user'] = false;

        $this->auth_checked = true;

        //1) Делаем логин, если есть данные
        if ($this->login())
        {
            return true;
        }

        //2) Проверяем авторизацию
        if ($id = $this->getSessionId())
        {
            //Открываем сессию
            if ($session = $this->sessionModel->getData($id))
            {
                if (isset($session['login'])  && isset($session['password']) && isset($session['hash']))
                {
                    $login = $session['login'];
                    $password = $session['password'];
                    $hash = $session['hash'];

                    //Достаем пользователя с данным логином
                    $res = $this->userModel->getByLogin($login);

                    //Если существует и пароль подходит
                    if ($res && ($password == $res['password']) &&
                        ($hash == noxUserModel::hash($password . $_SERVER['HTTP_USER_AGENT']))
                    )
                    {
                        //Авторизуем
                        $this->auth = true;

                        //Записываем ID и имя пользователя
                        $this->user_id = $res['id'];
                        $this->user_name = $res['login'];
                        unset($res['password']);

                        //Загружаем список групп пользователя
                        $model = new noxUserGroupsModel();
                        $res['groups'] = $model->getByUser($res['id']);

                        $this->user = $res;
                        $GLOBALS['vars']['user'] = $res;

                        //Обновляем дату последнего пребывания на сайте
                        $this->userModel->updateLastVisit($res['id']);
                        return true;
                    }
                }
            }
        }

        //Иначе, данные неверные
        //Больше проверять авторизацию не требуется
        $this->logout();
        $this->auth = false;
        return false;
    }

    /**
     * Вход по логину и паролю
     * @param string $login_or_email
     * @param string $password
     * @param mixed $user_type
     * @param bool $force
     * @return bool
     */
    public function login($login_or_email = '', $password = '', $user_type = false, $force = false)
    {
        //Существуют ли переменные логина и пароля
        if ((empty($login_or_email)) || empty($password))
        {
            if (isset($_POST['login']) && isset($_POST['password']))
            {
                $login_or_email = $_POST['login'];
                $password = $_POST['password'];
            } else
                if (isset($_POST['email']) && isset($_POST['password']))
                {
                    $login_or_email = $_POST['email'];
                    $password = $_POST['password'];
                }
        }

        if (!empty($login_or_email) && (!empty($password) || $force))
        {
            //Получаем хеш пароля
            $password = noxUserModel::hash($password);

            //Достаем пользователя с данным логином
            $whereTmp = '") AND ((`registration_status`="success_confirm") OR (`user_type`="facebook"))';
            $res = $this->userModel->reset()->where('(`login`="' .  $login_or_email . $whereTmp)->fetch();
            if (!$res)
            {
                $res = $this->userModel->reset()->where('(`email`="' .  $login_or_email . $whereTmp)->fetch();
            }
            if($force) {
                $password = $res['password'];
            }

            //Если существует и пароль подходит
            if ($res && ($password == $res['password']))
            {
                //Авторизуем
                $this->auth = true;
                $this->auth_checked = true;

                //Записываем ID и имя пользователя
                $this->user_id = $res['id'];
                $this->user_name = $res['login'];
                unset($res['password']);

                //Загружаем список групп пользователя
                $model = new noxUserGroupsModel();
                $res['groups'] =  $model->getByUser($res['id']);

                $this->user = $res;
                $GLOBALS['vars']['user'] = $res;

                //Сохраняем данные в сессию
                $session['login'] = $res['login'];
                $session['password'] = $password;
                //Записываем hash, чтобы пользователь мог зайти только с текущего браузера и ОС
                $session['hash'] = noxUserModel::hash($password . $_SERVER['HTTP_USER_AGENT']);
                $id = $this->getSessionId(true);

                //Удаляем старые сессии
                $this->sessionModel->deleteOldData();

                if ($this->sessionModel->saveData($id, $session))
                {
                    $config = noxConfig::getConfig();
                    $domain = (isset($config['cookie_domain']) && !empty($config['cookie_domain'])) ? $config['cookie_domain'] : $_SERVER['SERVER_NAME'];

                    //Записываем ID сессии в cookie
                    setcookie('nox_sid', $id, time() + 60 * 60 * 24 * 255, noxSystem::$baseUrl. '/', $domain);
                }

                return true;
            }
        }
        $this->auth = false;
        $this->auth_checked = true;
        return false;
    }

    /**
     * Выход
     *
     * @return bool результат
     */
    public function logout()
    {
        if ($id = $this->getSessionId())
        {
            //Удаляем сессию
            $this->sessionModel->deleteById($id);
        }
        if (isset($_COOKIE['nox_sid']))
        {
            unset($_COOKIE['nox_sid']);
            $config = noxConfig::getConfig();
            $domain = (isset($config['cookie_domain']) && !empty($config['cookie_domain'])) ? $config['cookie_domain'] : $_SERVER['SERVER_NAME'];

            setcookie('nox_sid', '', time(), noxSystem::$baseUrl. '/', $domain);
        }
        return true;
    }

    /**
     * Возвращает ID авторизованного пользователя
     *
     * @return int
     */
    public function getUserId()
    {
        if (!$this->user_id)
        {
            if ($this->authorization())
            {
                return $this->user_id;
            } else
            {
                return 0;
            }
        }
        else
        {
            return $this->user_id;
        }
    }

    /**
     * Возвращает имя авторизованного пользователя
     *
     * @return string
     */
    public function getUserName()
    {
        if (!$this->user_name)
        {
            if ($this->authorization())
            {
                return $this->user_name;
            } else
            {
                return 0;
            }
        }
        else
        {
            return $this->user_name;
        }
    }

    /**
     * Возвращает проифиль авторизованного пользователя
     *
     * @return array
     */
    public function getUser()
    {
        if (!$this->user)
        {
            if ($this->authorization())
            {
                return $this->user;
            } else
            {
                return false;
            }
        }
        else
        {
            return $this->user;
        }
    }

    /**
     * Проверяет, имеет ли пользователь право
     *
     * @param $module  string модуль, которому необходимо право
     * @param $right   string идентификатор права
     * @return bool имеет ли доступ
     */
    public function haveRight($module, $right)
    {
        $ar = $this->getUser();
        if (!$ar)
        {
            return false;
        }

        //Проверяем права
        return $this->rightModel->haveRight($module, $right, $ar['groups']);
    }

    /**
     * Добавляет право для группы
     *
     * @param $module   string модуль, которому необходимо право
     * @param $right    string идентификатор права
     * @param $group_id int ID группы
     * @return bool результат
     */
    public function addRight($module, $right, $group_id)
    {
        if (!$this->haveRight($module, $right, $group_id))
        {
            //Создаем запрос на добавление
            $this->rightModel->addRight($module, $right, $group_id);
        }
        return true;
    }

    /**
     * Удаляет право для группы
     *
     * @param $module   string модуль, которому необходимо право
     * @param $right    string идентификатор права
     * @param $group_id int ID группы
     * @return bool результат
     */
    public function deleteRight($module, $right, $group_id)
    {
        return $this->rightModel->deleteRight($module, $right, $group_id);
    }

    /**
     * Возвращает массив прав, данных группы
     *
     * @param $group_id int идентификатор группы
     * @return array результат
     */
    public function getRights($group_id)
    {
        //Проверяем права
        return $this->rightModel->getByGroup($group_id);
    }

    /**
     * Проверяет, является ли пользователь членом группы
     *
     * @param $group
     * @param int $user_id 0 для текущего пользователя
     * @return bool
     */
    public function userInGroup($group, $user_id=0)
    {
        //Если задано имя группы
        if (is_string($group))
        {
            //Составляем список групп
            static $groups = array();
            if (!$groups)
            {
                $groupModel = new noxGroupModel();
                while ($ar = $groupModel->fetch())
                {
                    $groups[strtolower($ar['name'])] = $ar['id'];
                }
            }
            //ПОлучаем по имени ID
            $group_id = @$groups[strtolower($group)];
        } else
        {
            //Или просто берем ID
            $group_id = intval($group);
        }

        if (!$group_id)
        {
            return false;
        }

        if (!$user_id)
        {
            if ($user = $this->getUser())
            {
                return isset($user['groups'][intval($group_id)]);
            }
            return false;
        } else
        {
            $model = new noxUserGroupsModel();
            $model->where(array(
                'group_id' => $group_id,
                'user_id' => $user_id
            ));

            if ($model->fetch())
            {
                return true;
            } else
            {
                return false;
            }
        }
    }
}

?>