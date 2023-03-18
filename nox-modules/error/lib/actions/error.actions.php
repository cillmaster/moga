<?php
/**
 * Действия модуля для вывода сообщений об ошибках
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    nox-error
 * @subpackage error
 */

class errorActions extends noxThemeActions
{
    public $cache = false;

    public function execute()
    {
        noxSystem::$params['frontend'] = true;
        noxSystem::$params['backend'] = false;
        if(noxSystem::$urlArray[0] === 'administrator') {
            $this->setTheme('administrator');
        }
        return parent::execute();
    }

    /**
     * Статус 200. OK (200 OK)
     *
     */
    public function action200()
    {
        //Выводим заголовки
        header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        header('Status: 200 OK');
    }

    /**
     * Статус 202. Принято (202 Accepted)
     *
     */
    public function action202()
    {
        //Выводим заголовки
        header($_SERVER['SERVER_PROTOCOL'] . ' 202 Accepted');
        header('Status: 202 Accepted');
    }

    /**
     * Статус 202. Принято (202 Accepted)
     *
     */
    public function action301($to)
    {
        //Выводим заголовки
        header("Location: $to", true, 301);
        exit;
    }

    /**
     * Ошибка 400. Плохой запрос (400 Bad Request)
     *
     */
    public function action400()
    {
        //Выводим заголовки
        header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
        header('Status: 400 Bad Request');

        if ($this->params['ajax'])
        {
            echo '400 Bad Request';
        } else
        {
            //Выводим контент
            $this->caption = '400 Bad Request';
            echo '<h2>Error 400</h2><br />Bad Request!';
        }
    }

    /**
     * Ошибка 401. Не авторизован (401 Unauthorized)
     *
     */
    public function action401()
    {
        //Выводим заголовки
        header($_SERVER['SERVER_PROTOCOL'] . ' 401 Unauthorized');
        header('Status: 401 Unauthorized');

        if ($this->params['ajax'])
        {
            echo '401 Unauthorized';
        } else
        {
            //Выводим контент
            $this->caption = '401 Unauthorized';

            echo '<h2>Error 401</h2><br />Need authorization or no rights!<br /><br />
		<form method="POST" action="">Login:<br /><input type="text" name="login" id="login-form" />
		<br /><br />Password:<br /><input type="password" name="password" />
		<br /><br /><input type="submit" value="Enter" /></form>';
        }
    }


    /**
     * Ошибка 404. Не найдено
     *
     */
    public function action404()
    {
        //Выводим заголовки
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        header('Status: 404 Not Found');

        if ($this->params['ajax'])
        {
            echo '404 Not Found';
        } else
        {
            //Выводим контент
            $this->caption = '404 Not Found';
            echo '<h2>Error 404</h2><br />Page &quot;' . noxSystem::$requestUrl . '&quot; not found.';
        }
    }

    /**
     * Ошибка 500. Внутренняя ошибка сервера (500 Internal Server Error)
     *
     */
    public function action500()
    {
        //Выводим заголовки
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        header('Status: 500 Internal Server Error');

        if ($this->params['ajax'])
        {
            echo '500 Internal Server Error';
        } else
        {
            //Выводим контент
            //$this->caption = '500 Internal Server Error';
            echo '<div class="pre-content"><div class="container_12"><h2>Oops, we got some technical updates. Please refresh our website in next few minutes.</h2>
                <div style="color:#eee;">500 Internal Server Error</div></div></div>';
        }
    }
}

?>