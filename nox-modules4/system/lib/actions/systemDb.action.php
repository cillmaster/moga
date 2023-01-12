<?php
/**
 * Действие для отображения и сохранения настроек БД
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    system
 * @subpackage db
 */

class systemDbAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Настройка БД';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Записываем права админа в сессию
        noxSystem::$userControl->writeToSession('db_admin', 1);

        if (isset($_POST['key']))
        {
            $c = count($_POST['key']);
            $temp = array();
            for ($i = 0; $i < $c; $i++)
            {
                $key = $_POST['key'][$i];
                $temp[$key]['type'] = $_POST['type'][$i];
                $temp[$key]['db'] = $_POST['db'][$i];
                $temp[$key]['login'] = $_POST['login'][$i];
                $temp[$key]['password'] = $_POST['password'][$i];
                $temp[$key]['host'] = $_POST['host'][$i];
            }
            //Сохраняем
            noxConfig::saveDb($temp);
            noxSystem::location();
        }

        //Добавляем переменные
        $this->addVar('dbArray', noxConfig::getDb());
    }
}

?>