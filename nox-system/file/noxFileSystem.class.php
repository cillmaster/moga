<?php
/**
 * noxFileSystem
 *
 * Класс noxFileSystem, служащий для управления файловой системой
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2.1
 * @package    nox-system
 * @subpackage file
 */

class noxFileSystem
{
    /**
     * Ищет файлы по фильтру в каталоге
     *
     * @param string Фильтр
     * @param string Каталог
     * @param bool $recursion Рекурсивный поиск
     * @return array
     */
    public static function findFiles($filter, $dir, $recursion = true, $urlDir=false)
    {
	    $dir = self::getRealPathFromUrl(rtrim($dir, '\//').'/').'/';

        if (!$urlDir)
        {
	        $urlDir = $dir;
        }
        else
        {
            $urlDir = rtrim($urlDir, '\//').'/';
        }

        if (!$dir or !$filter)
        {
            return false;
        }

        //Если папка не существует
        if (!is_dir($dir))
        {
            return false;
        }

        $res = array();

        //Если поиск рекурсивный, то ищем все каталоги в текущем
        if ($recursion)
        {
            foreach (glob($dir . '*', GLOB_ONLYDIR | GLOB_MARK) as $new_dir)
            {
                $b = basename($new_dir);
                //И добавляем к результатам поиск в найденом каталоге
                $res = array_merge($res, self::FindFiles($filter, $urlDir.$b, true, $urlDir.$b));
            }
        }

        //Ищем файлы
        foreach (glob($dir . $filter, GLOB_MARK) as $filename)
        {
            if (is_file($filename))
            {
                $res[] = $urlDir.basename($filename);
            }
        }
        return $res;
    }

    /**
     * Преобразовывает URL в нормальный путь
     * @static
     * @param string $folder папка
     * @return string
     */
    public static function getRealPathFromUrl($url)
    {
        static $path = '';
        if (empty($path))
        {
            $path = noxRealPath('');
        }

        $url = rtrim($url, '/\\');

        if (substr($url, 0, strlen($path)) != $path)
        {
            $url = noxRealPath($url);
        }
        return $url;
        /*
        static $l = 0;
        if (!$l)
        {
            $path = noxRealPath('');
            $l = strlen($path);
        }
        if (noxSystem::$baseUrl && strstr($folder, noxSystem::$baseUrl))
        {
            $folder = substr($folder, strlen(noxSystem::$baseUrl));
        }
        $folder = trim(substr(noxRealPath($folder), $l), '\//');
        return $folder;*/
    }

    /**
     * Возращает листинг файлов и (или) папок
     *
     * @static
     * @param $folder Папка для листинга
     * @param int $mode 0 - папки и файлы, 1 - папки, 2 - файлы
     * @return array
     */
    public static function listing($folder, $mode = 0)
    {
        //_d($folder);
        //exit();
        if (!is_dir($folder))
        {
            return 0;
        }

        $folders = array();
        $files = array();

        //Проверяем, является ли директорией
        if (is_dir($folder))
        {
            //Проверяем, была ли открыта директория
            if ($dir = opendir($folder))
            {
                //Сканируем директорию
                while ($file = readdir($dir))
                {
                    //Убираем лишние элементы
                    if (($file != ".") && ($file != ".."))
                    {

                        $perms = fileperms($folder . '/' . $file);

                        if (($perms & 0xC000) == 0xC000)
                        {
                            // Сокет
                            $info = 's';
                        } elseif (($perms & 0xA000) == 0xA000)
                        {
                            // Символическая ссылка
                            $info = 'l';
                        } elseif (($perms & 0x8000) == 0x8000)
                        {
                            // Обычный
                            $info = '-';
                        } elseif (($perms & 0x6000) == 0x6000)
                        {
                            // Специальный блок
                            $info = 'b';
                        } elseif (($perms & 0x4000) == 0x4000)
                        {
                            // Директория
                            $info = 'd';
                        } elseif (($perms & 0x2000) == 0x2000)
                        {
                            // Специальный символ
                            $info = 'c';
                        } elseif (($perms & 0x1000) == 0x1000)
                        {
                            // Поток FIFO
                            $info = 'p';
                        } else
                        {
                            // Неизвестный
                            $info = 'u';
                        }

                        $p = substr(sprintf('%o', $perms), -4);

                        //Если папка, то записываем значение в массив $folders
                        if ($info == 'd')
                        {
                            $folders[] = array('name'  => $file,
                                               'perms' => $p,
                                               'ext'   => 'dir');
                        }
                        //Если файл, то пишем в массив $files
                        else
                        {
                            $files[] = array('name'  => $file,
                                             'perms' => $p,
                                             'size'  => filesize($folder . '/' . $file),
                                             'ext'   => strtolower(substr(strrchr($file, '.'), 1)));
                        }
                    }
                }
            }
            //Закрываем директорию
            closedir($dir);
        }

        if ($folders)
        {
            usort($folders, create_function('$a,$b', 'return strcmp($a[\'name\'], $b[\'name\']);'));
        }
        if ($files)
        {
            usort($files, create_function('$a,$b', 'return strcmp($a[\'name\'], $b[\'name\']);'));
        }

        //Если режим = 0 то возвращаем массив с папками + файлы
        if ($mode == 0)
        {
            return array($folders, $files);
        }
        //Если режим = 1 то возвращаем массив с папками
        if ($mode == 1)
        {
            return array($folders);
        }
        //Если режим = 2 то возвращаем массив с файлами
        if ($mode == 2)
        {
            return array($files);
        }

        return false;
    }

    /**
     * Переименование файла\папки
     * @static
     * @param $path
     * @param $new_name
     * @return bool
     */
    public static function rename($path, $new_name)
    {
        return rename($path, dirname($path) . '/' . $new_name);
    }

    /**
     * Удаляет файл/папку со всем её содержимым
     * @static
     * @param $path
     * @return bool
     */
    public static function delete($path)
    {
        if (empty($path) || $path == '/')
            return false;

        if (is_dir($path))
        {
            //Защита от непредвиденного удаления кучи всего
            //Чистим папку от файлов
            self::clearFolder($path);
            return rmdir($path);
        }
        else
        {
            return @unlink($path);
        }
    }

    /**
     * Очищает содержимое папки
     * @static
     * @param $path
     * @return bool
     */
    public static function clearFolder($path)
    {
        if (empty($path) || $path == '/')
            return false;

        if (is_dir($path))
        {
            $path = rtrim($path, '/\\');
            if ($dir = opendir($path))
            {
                //Сканируем директорию
                while ($file = readdir($dir))
                {
                    //Убираем лишние элементы
                    if ($file != "." && $file != "..")
                    {
                        $p = $path . '/' . $file;
                        //Если папка, то рекурсивно чистим и её
                        if (is_dir($p))
                        {
                            self::clearFolder($p);
                            //и удаляем
                            rmdir($p);
                        }
                        elseif (file_exists($p))
                        {
                            @unlink($p);
                        }
                    }
                }
            }
            //Закрываем директорию
            closedir($dir);
            return true;
        }
        return false;
    }

    /**
     * Устанавливает права на файл/папку
     * @static
     * @param $path
     * @param $perms
     * @return bool
     */
    public static function chmod($path, $perms)
    {
        return chmod($path, octdec($perms));
    }

    /**
     * Создает рекурсивно папку
     * @static
     * @param $url
     * @return bool
     */
    public static function createFolder($url)
    {
        if (!is_dir($url))
            return mkdir($url, 0777, true);
        else
            return true;
    }

    /**
     * Загружает временный файл под определенным именем
     * @static
     * @param $temp
     * @param $url
     * @return bool
     */
    public static function upload($temp, $url)
    {
        $dir = dirname($url);

        if (!is_dir($dir))
        {
            if(preg_match('/.*\/vectors\/[^\/]*\/[^\/]*\/(store|preview)$/', $dir, $match)){
                $dir2 = preg_replace('/(store|preview)$/', $match[1] == 'store' ? 'preview' : 'store', $dir);
                if (!is_dir($dir2)){
                    self::createFolder($dir2);
                }
            }
            self::createFolder($dir);
        }

        return move_uploaded_file($temp, $url);
    }

    /**
     * Загружает содержимое файла по адресу и возвращает содержимое
     * @static
     * @param $url
     * @return string
     */
    public static function downloadFromUrl($url)
    {
        if (ini_get('allow_url_fopen'))
        {
            //Если разрешено загружать файлы из URL
            return file_get_contents($url);
        } elseif (function_exists('curl_init'))
        {
            //Иначе, если установлен curl
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
        } else
        {
            //Иначе мы не можем загрузить файл
            return false;
        }
    }

    /**
     * Загружает содержимое файла по адресу и сохраняет в файл
     * @static
     * @param $url
     * @return string
     */
    public static function downloadFromUrlToFile($url, $fileName)
    {
        return file_put_contents($fileName, self::downloadFromUrl($url));
    }

    /**
     * Возвращает содержимое текстового файла
     * @static
     * @param $url
     * @return string
     */
    public static function getTextFile($url)
    {
        return file_get_contents($url);
    }

    /**
     * Сохраняет содержимое текстового файла
     * @static
     * @param $url
     * @param $text
     * @return int
     */
    public static function saveTextFile($url, $text)
    {
        return file_put_contents($url, $text);
    }

    /**
     * Выводит файл в браузер для скачивания
     * @static
     * @param $url
     * @param $filename string Имя файла, которое будет выведено в браузер
     */
    public static function downloadFile($url, $filename='')
    {
        if (!file_exists($url))
        {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
            header('Status: 404 Not Found');
            exit();
        }

        if (!$filename) $filename = basename($url);

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Type: ' . self::getMime($url));
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.intval(filesize($url)));

        // Открываем искомый файл
        $f = fopen($url, 'r');
        while (!feof($f))
        {
            // Читаем килобайтный блок, отдаем его в вывод и сбрасываем в буфер
            echo fread($f, 1024);
            flush();
        }
        // Закрываем файл
        fclose($f);

        exit;
    }

    /**
     * Возвращает mimi файла
     * @static
     * @param string $url
     * @return string
     */
    public static function getMime($url)
    {
        // our list of mime types
        static $mime_types = array(
            'pdf'  => 'application/pdf',
            'exe'  => 'application/octet-stream',
            'zip'  => 'application/zip',
            'docx' => 'application/msword',
            'doc'  => 'application/msword',
            'xls'  => 'application/vnd.ms-excel',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'gif'  => 'image/gif',
            'png'  => 'image/png',
            'jpeg' => 'image/jpg',
            'jpg'  => 'image/jpg',
            'mp3'  => 'audio/mpeg',
            'wav'  => 'audio/x-wav',
            'mpeg' => 'video/mpeg',
            'mpg'  => 'video/mpeg',
            'mpe'  => 'video/mpeg',
            'mov'  => 'video/quicktime',
            'avi'  => 'video/x-msvideo',
            '3gp'  => 'video/3gpp',
            'css'  => 'text/css',
            'jsc'  => 'text/javascript',
            'js'   => 'text/javascript',
            'php'  => 'application/x-httpd-php',
            'htm'  => 'text/html',
            'html' => 'text/html',
            'sql'  => 'text/x-plsql',
            'txt'  => 'text/plain',
        );

        $ext = strtolower(substr(strrchr($url, '.'), 1));

        if (isset($mime_types[$ext]))
        {
            return $mime_types[$ext];
        } else
        {
            return 'application/octet-stream';
        }
    }
}

?>