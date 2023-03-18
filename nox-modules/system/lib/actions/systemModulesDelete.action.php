<?php
/**
 * Действие для работы с модулями
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    system
 * @subpackage modules
 */

class systemModulesDeleteAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Удаление модуля';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Установленные модули
        $modules = noxConfig::getModules();

        $moduleName = getParam(@$_GET['module'], '');

        if (empty($moduleName))
        {
            return 400;
        }

        $configPath = noxRealPath('nox-modules/' . $moduleName . '/lib/config');

        $text = '<ul>';

        //Получаем информацию о модуле
        $text .= '<li>Получение информации о модуле...</li>';
        $info = include($configPath . '/info.php');

        $path = $configPath . '/delete.sql';
        if (file_exists($path))
        {
            $text .= '<li>Удаление таблиц из БД...</li>';
            //Включаем файл установки
            $query = new noxDbQuery();
            $query->execMultiQuery(file_get_contents($path));
        }


        //Удаляем блоки
        if (isset($modules[$moduleName]['blocks']))
        {
            $text .= '<li>Удаление блоков...</li>';
            foreach ($modules[$moduleName]['blocks'] as $name => $array)
            {
                $text .= '<li>Удаление блока '.$name.'...</li>';
                noxConfig::deleteBlock($name);
            }
        }

        unset($modules[$moduleName]);

        if (noxConfig::saveModules($modules))
        {
            $text .= '<li>Запись информации о модуле...</li>';
        } else
        {
            throw new noxException('Ошибка сохранения модулей!');
        }

        //Добавялем права для модуля
        $model = new noxGroupRightsModel();
        $model->deleteByModule($moduleName);
        $text .= '<li>Удаление прав, связанных с модулем...</li>';

        $text .= '<li class="green">Модуль удален!</li></ul>';

        echo $text;
    }
}

?>