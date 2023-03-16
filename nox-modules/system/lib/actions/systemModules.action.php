<?php
/**
 * Действие для работы с модулями
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    system
 * @subpackage menu
 */

class systemModulesAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Модули';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Установленные модули
        $modules = noxConfig::getModules();

        //Ищем модули в папке /nox-modules
        $folders = noxFileSystem::listing(noxRealPath('nox-modules'), 1);

        foreach ($folders[0] as $ar)
        {
            //Имя модуля в соответствии с папкой
            $moduleName = $ar['name'];

            $infoPath = 'nox-modules/' . $moduleName . '/lib/config/info.php';

            if (!file_exists($infoPath))
            {
                continue;
            }
            $info = include($infoPath);

            array_walk($info, create_function('&$item, $key', 'if (is_string($item)) $item = htmlspecialchars($item);'));

            $info['name'] = $moduleName;
            //Установлен ли модуль?
            $info['install'] = @isset($modules[$moduleName]);
            if ($info['install'])
            {
                $info['installVersion'] = $modules[$moduleName]['version'];
            }

            $modules_info[] = $info;
        }

        $this->addVar('modules', $modules_info);
    }
}

?>