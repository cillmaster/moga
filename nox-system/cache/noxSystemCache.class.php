<?php

/**
 * Класс системного кеша
 *
 * Отличается от обычного тем, что работает всегда
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage cache
 */
class noxSystemCache
{

    /**
     * Возвращает хеш имени
     *
     * @param string
     * @return string
     */
    public static function getHash($name)
    {
        return md5($name);
    }

    /**
     * Кэширует информацию $content под идентификатором $name
     *
     * @param string $name   уникальный идентификатор кешируемой информации
     * @param mixed $content информация, которую необходимо сохранить
     * @return bool
     */
    public static function create($name, $content)
    {
        //Проверяем правильность параметров
        if (!$name || !$content)
        {
            return false;
        }

        $folder = noxRealPath('nox-cache');
        //_d($folder);

        //Если в файл нельзя записать
        if (!is_writable($folder))
        {
            chmod($folder, 0777);
        }

        return (file_put_contents($folder . '/' . self::getHash($name), serialize($content), LOCK_EX) > 0);
    }

    /**
     * Возвращает информацию, сохраненную под идентификатором
     *
     * @param string $name      уникальный идентификатор кешируемой информации
     * @param int $expTime      временной промежуток в секундах, после которого информация считается устаревшей, т.е. несуществуемой
     * @param bool $expTimeFull если true, то $expTime берется как
     * @return mixed информация, либо false в случае отсутствия кэша
     */
    public static function get($name, $expTime = 0, $expTimeFull = false)
    {
        //Проверяем правильность параметров
        if (!$name)
        {
            return false;
        }

        //Получаем хеш имени
        $name = self::getHash($name);

        $filename = noxRealPath('nox-cache/' . $name);
        //Если файл не существует
        if (!file_exists($filename))
        {
            return false;
        }

        if ($expTime === 0)
        {
            return @unserialize(file_get_contents($filename));
        } else
        {

            //Получаем время последнего изменения файла
            $time = filemtime($filename);
            if (!$expTimeFull)
            {
                $expTime = time() - $expTime;
            }

            //Если информация не устарела
            if ($expTime <= $time)
            {
                //Читаем файл и возвращаем сохраненную информацию
                return @unserialize(file_get_contents($filename));
            } else
            {
                //Иначе, удаляем файл
                @unlink($filename);
                return false;
            }
        }
    }

    /**
     * Очищает информацию, сохраненную под идентификатором
     *
     * @param string $name уникальный идентификатор кешируемой информации
     * @return bool
     */
    public static function clear($name)
    {
        //Проверяем правильность параметров
        if (!$name)
        {
            return false;
        }

        //Получаем хеш имени
        $name = self::getHash($name);

        $filename = noxRealPath('nox-cache/' . $name);
        //Если файл не существует
        if (!file_exists($filename))
        {
            return false;
        }
        @unlink($filename);
        return true;
    }
}

?>