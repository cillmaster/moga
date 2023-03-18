<?php
/**
 * Действие редактирования файла
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    filemanager
 */

class filemanagerAdministratorEditAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Редактирование файла';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право на редактирование страниц
        if (!$this->haveRight('data'))
        {
            return 401;
        }

        if (!isset($_GET['path']))
        {
            return 400;
        } else
        {
            $url = '/'. trim(urldecode($_GET['path']), '/\\');
        }

        $path = noxFileSystem::getRealPathFromUrl($url);
        if (empty($path))
        {
            return 400;
        }

        //Проверяем, является ли папка не nox-data
        if (substr($url, 0, 9) != '/nox-data')
        {
            if (!$this->haveRight('all'))
            {
                return 401;
            }
        }

        if (isset($_POST['text']))
        {
            noxFileSystem::saveTextFile($path, $_POST['text']);
            noxSystem::location('?folder=' . urlencode(dirname($url)));
        }

        //Добавляем переменные

        $this->addVar('path', $path);
        $this->addVar('url', $url);
        $this->addVar('text', noxFileSystem::getTextFile($path));

        $ext = strtolower(substr(strrchr($path, '.'), 1));
        $this->addVar('ext', $ext);
        $this->addVar('mime', noxFileSystem::getMime($path));

        //Если это ajax запрос
        if (!$this->params['ajax'])
        {
            //Формируем страницу
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/codemirror.js';
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/xml.js';
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/clike.js';
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/php.js';
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/css.js';
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/javascript.js';
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/htmlmixed.js';
            $js[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/js/codemirror/plsql.js';
            $css[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/css/codemirror/codemirror.css';
            $css[] = noxSystem::$baseUrl . '/' . $this->moduleFolder . '/css/codemirror/Administrator.css';
            $this->addJs($js);
            $this->addCss($css);

            $this->title = 'Редактирование файла ' . basename($path);
            $this->caption = 'Редактирование файла ' . basename($path) . ' в папке <a href="?folder=' . urlencode(dirname($url)) . '">' . dirname($url) . '</a>';
        }
    }
}

?>