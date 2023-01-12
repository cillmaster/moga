<?php
/**
 * Действие для работы с модулями
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    system
 * @subpackage modules
 */

class systemModulesInstallAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Установка модуля';

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


        $path = $configPath . '/install.sql';
        if (file_exists($path))
        {
            $text .= '<li>Установка БД...</li>';
            //Включаем файл установки
            $query = new noxDbQuery();
            $query->execMultiQuery(file_get_contents($path));
        }

        $save_info = $info;
        $save_info['install_date'] = date('Y-m-d H:i:s');

        //Устанавливем блоки
        if (isset($info['blocks']))
        {
            $text .= '<li>Установка блоков...</li>';
            foreach ($info['blocks'] as $name => $array)
            {
                if (!isset($array['module']))
                {
                    $array['module'] = $moduleName;
                }
                $text .= '<li>Установка блока '.$name.'...</li>';
                noxConfig::addBlock($name, $array);
            }
        }

        $modules[$moduleName] = $save_info;

        if (noxConfig::saveModules($modules))
        {
            $text .= '<li>Запись информации о модуле...</li>';
        } else
        {
            throw new noxException('Ошибка сохранения модулей!');
        }

        //Добавялем права для модуля
        $model = new noxGroupRightsModel();
        if (isset($info['rights']) && $info['rights'])
        {
            foreach ($info['rights'] as $right => $d)
            {
                $model->addRight($moduleName, $right, 1);
            }
            $text .= '<li>Задание прав администратора для группы администраторов...</li>';
        }

        $text .= '<li class="green">Модуль установлен!</li></ul>';

        echo $text;
    }
}

?>