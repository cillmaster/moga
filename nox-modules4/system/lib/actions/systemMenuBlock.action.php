<?php
/**
* Действие для отображения блока меню панели администратора
* 
* @author Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
* @version 1.1
* @package system
* @subpackage menu
*/

class systemMenuBlockAction extends noxAction
{		
	public $cache = false;

    private function menu($menu, $level, $module='')
    {
        //Текущий URL
        $url = noxSystem::$requestUrl;

        $text = '<ul>';
        foreach ($menu as $ar)
        {
            $needRight = isset($ar['right']) ? $ar['right'] : 'control';

            if ($level<=1) $module = $ar['module'];

            if (!empty($needRight))
            {
                if (!noxSystem::haveRight($module, $needRight))
                {
                    continue;
                }
            }

            $active = preg_match('/'.preg_quote($ar['link'], '/').'/si', $url) ? ' class="active"' : '';

            $text .= '<li'.$active.'><a href="'.noxSystem::$baseUrl.$ar['link'].'" title="'.$ar['title'].'">'.$ar['title'].'</a>';
            if (isset($ar['childs']) && (count($ar['childs'])>0))
            {
                $text .= $this->menu($ar['childs'], $level+1, $module);
            }
            $text .= '</li>';
        }
        return $text.'</ul>';
    }
	
	public function execute()
	{
        //Получаем список модулей
        $modules = noxConfig::getModules();
        $menu = array();

        foreach ($modules as $name=>$module)
        {
            //Если у модуля выключен backend, то не выводим его
            if (!isset($module['backend']) || !$module['backend'])
            {
                continue;
            }

            //Если меню задано явно
            if (isset($module['menu']))
            {
                $m = $module['menu'];
                foreach ($m as &$a)
                {
                    if (isset($a['title']))
                    {
                        $a['module'] = $name;
                    }
                }

                //Используем его
                $menu = array_merge($menu, $m);
            } else
            {
                //Иначе делаем своё
                $menu[] = array('title' => $module['title'],
                            'link' => '/administrator/'.$name,
                            'module' => $name,
                            'childs' => array());
            }
        }
		echo $this->menu($menu, 1);

	}
}

?>