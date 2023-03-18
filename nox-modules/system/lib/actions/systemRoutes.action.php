<?php
/**
 * Действие для отображения и сохранения основных шаблонов
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    system
 * @subpackage menu
 */

class systemRoutesAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Настройки маршрутизатора';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        if (isset($_POST['url']))
        {
            foreach ($_POST['domainName'] as $domain)
            {
                $domain = trim($domain);
                if (!empty($domain))
                {
                    $temp[$domain] = array();
                }
            }

            $c = count($_POST['url']);

            for ($i = 0; $i < $c; $i++)
            {
                $domain = $_POST['routeDomain'][$i];
                $newDomain = $_POST['domainName'][$domain];

                if (!isset($temp[$newDomain]))
                    continue;

                $ar = array(
                    'url'    => $_POST['url'][$i],
                    'module' => $_POST['module'][$i],
                    'enabled' => $_POST['enabled'][$i],
                );
                $temp[$newDomain][] = $ar;
            }

            //Сохраняем
            noxConfig::saveRoutes($temp);
            noxSystem::location();
        }

        //Добавляем переменные
        $this->addVar('routes', noxConfig::getRoutes());

        $modules = array();
        foreach (noxConfig::getModules() as $key=>$ar)
        {
            if (isset($ar['frontend']) && $ar['frontend'])
            {
                $modules[$key] = $ar['title'];
            }
        }
        $this->addVar('modules', $modules);

        $this->addCss($this->moduleFolder.'/css/routes.css');
        $this->addJs($this->moduleFolder.'/js/routes.js');
    }
}

?>