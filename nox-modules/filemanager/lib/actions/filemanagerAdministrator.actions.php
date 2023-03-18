<?php
/**
 * Действия для изменения параметров файлов, не требующие вывода
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    filemanager
 */

class filemanagerAdministratorActions extends noxActions
{
    public $cache = false;

    public function actionCreatefolder()
    {
        //Проверяем, есть ли у пользователя право
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

        noxFileSystem::createFolder($path . '/' . urldecode($_GET['name']));
        return 200;
    }

    public function actionDelete()
    {
        //Проверяем, есть ли у пользователя право
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

        noxFileSystem::delete($path);

        return 200;
    }

    public function actionChmod()
    {
        //Проверяем, есть ли у пользователя право
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

        noxFileSystem::chmod($path, $_GET['perms']);
        return 200;
    }

    public function actionRename()
    {
        //Проверяем, есть ли у пользователя право
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

        noxFileSystem::rename($path, $_GET['name']);
        return 200;
    }

    public function actionDownload()
    {
        //Проверяем, есть ли у пользователя право
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

        noxFileSystem::downloadFile($path);
        return 200;
    }
}

?>