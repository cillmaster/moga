<?php
/**
 * Действие для отображения блока меню панели администратора
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    pages
 * @subpackage menu
 */

class pagesMenuBlockAction extends noxAction
{
    public $cache = false;

    public function execute()
    {
        //Начальный уровень
        $min_level = isset($this->params[1]) ? intval($this->params[1]) : 1;

        //Максимальный уровень
        $max_level = isset($this->params[2]) ? intval($this->params[2]) : 10;

        $menu_name = isset($this->params[0]) ? $this->params[0] : 0;

        //Создаем модель меню
        $model = new pagesMenuModel();

        if (is_numeric($menu_name))
        {
            $menu = $model->getByParentId(intval($menu_name));
        } else
        {
            if (!$menu_name)
            {
                return 400;
            }
            $menu = $model->getByParentTitle($menu_name);
        }

        if (!$menu)
        {
            return 404;
        }

        //Рекурсивная функция
        $rec_function = create_function('$menu, $level, $min_level, $max_level, $rec_function',
            '
			//Текущий URL
			$url = noxSystem::$requestUrl;
				
			$text = \'\';
			foreach ($menu as $ar)
			{
				//Заменяем переменные в ссылке
				$ar[\'link\'] = preg_replace("/\{(.*?)\}/e", "@\$GLOBALS[\'vars\'][substr(\'\\\\1\', 1)]", $ar[\'link\']);

				if (!empty($ar[\'preg\']))
				{
					$preg = preg_replace("/\{(.*?)\}/e", "@\$GLOBALS[\'vars\'][substr(\'\\\\1\', 1)]", $ar[\'preg\']);
				} else
				{
					$preg = $ar[\'link\'].\'*\';
				}

				$preg = \'/^\'.str_replace(\'*\', \'(.*?)\',  str_replace(\'/\', \'\/\', $preg)).\'$/is\';

				$active = (preg_match($preg, $url)) ? \'active\' : \'\';

				if ($level>=$min_level)
				{
				
					$text .= \'<li class="\'.$ar[\'css_class\'].\' \'.$active.\'"><a href="'.noxSystem::$baseUrl.'\'.$ar[\'link\'].\'"><span>\'._t($ar[\'title\']).\'</span></a>\';
					
					if (($level<$max_level) && isset($ar[\'childs\']) && (count($ar[\'childs\'])>0))
					{
						$text .= $rec_function($ar[\'childs\'], $level+1, $min_level, $max_level, $rec_function);
					}
					$text .= \'</li>\';
					
				} elseif ($active)
				{
					if (($level<$max_level) && isset($ar[\'childs\']) && (count($ar[\'childs\'])>0))
					{
						return $rec_function($ar[\'childs\'], $level+1, $min_level, $max_level, $rec_function);
					}
				}			
			}
			return \'<ul>\'.$text.\'</ul>\';
		
		');

        $res = $rec_function($menu, 1, $min_level, $max_level, $rec_function);
        echo $res;
    }
}

?>