<?php
/**
 * Действие для отображения и сохранения основных шаблонов
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    system
 * @subpackage theme
 */

class systemThemesAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Настройка тем оформления';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        if (isset($_POST['name']))
        {
            $c = count($_POST['name']);
            for ($i = 0; $i < $c; $i++)
            {
                $key = $_POST['key'][$i];
                $temp[$key]['name'] = $_POST['name'][$i];
                $temp[$key]['folder'] = trim($_POST['folder'][$i],'/\\');
                $temp[$key]['filename'] = trim($_POST['filename'][$i],'/\\');
            }
            //Сохраняем
            noxConfig::saveThemes($temp);
            noxSystem::location();
        }

        //Добавляем переменные
        $this->addVar('templates', noxConfig::getThemes());
    }
}

?>