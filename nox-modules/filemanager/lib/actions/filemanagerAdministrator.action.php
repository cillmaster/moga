<?php
/**
 * Действие для отображения основного файлового менеджера
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    filemanager
 */

class filemanagerAdministratorAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Файловый менеджер';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право на редактирование страниц
        if (!$this->haveRight('data'))
        {
            return 401;
        }

        if (!isset($_GET['folder']))
        {
            $url = noxSystem::$baseUrl.'/nox-data';
        } else
        {
            $url = '/'. trim(urldecode($_GET['folder']), '/\\');
        }

        $path = noxFileSystem::getRealPathFromUrl($url);
        if (!is_dir($path))
        {
            $temp = dirname($path);
            if (is_dir($temp))
            {
                $url = dirname($url);
                $path = $temp;
            }
        }


        //Проверяем, является ли папка не nox-data
        if (substr($url, 0, 9) != '/nox-data')
        {
            if (!$this->haveRight('all'))
            {
                return 401;
            }
        }

        if (count($_FILES) > 0)
        {
            //Проверяем, есть ли у пользователя право на редактирование страниц
            if (!$this->haveRight('upload'))
            {
                return 401;
            }

            $res = array();

            foreach ($_FILES['uploads']['name'] as $k => $name)
            {
                noxFileSystem::upload($_FILES['uploads']['tmp_name'][$k], $path . '/' . $name);
                $res[] = $url . '/' . $name;
            }
            exit(json_encode($res));
            return 200;
        }

        //Добавляем переменные
        $temp = noxFileSystem::listing($path);

        $this->addVar('path', $path);
        $this->addVar('folder', $url);
        $this->addVar('subfolders', $temp[0]);
        $this->addVar('files', $temp[1]);
        $user_id = noxSystem::$userControl->user['id'];
        $this->addVar('canDelete', !in_array($user_id, [77787]));
    }
}

?>