<?php
/**
 * noxLocale
 *
 * Класс noxLocale, служащий для управления локалями и языками
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage core
 */

class noxLocale
{
    /**
     * Текущая локаль
     * @var string
     */
    public static $locale = '';

    /**
     * Словарь
     * @var array
     */
    public static $words = array();

    //TODO: Сделать работу локалей и проверить

    /**
     * Добавляет в массив слов данные из файла
     *
     * @param string Имя файла
     */
    public static function add($filename)
    {
        if (file_exists($filename))
        {
            $array = include($filename);
            if ($array)
                noxLocale::$words = @array_merge(noxLocale::$words, $array);
        }
    }

    /**
     * Добавляет в массив слов данные из файла
     *
     * @param string Имя файла
     */
    public static function setLocale($locale)
    {
        if ($locale != self::$locale)
        {
            self::$locale = $locale;
            //Очищаем старый перевод
            self::$words = array();

            $filename = noxRealPath('nox-locale/'.$locale.'.php');
            if (file_exists($filename))
            {
                $array = require($filename);
                if ($array)
                    noxLocale::$words = @array_merge(noxLocale::$words, $array);
            }
        }
    }

    /**
     * Возвращает перевод фразы
     *
     * Применение:
     * get('Hello') = Здравствуйте
     * get('%d минута', '%d минуты', '%d минуты', 51) - %d минута
     *
     * @return string Фраза на текущем языке
     */
    public static function get()
    {
        //Получаем количество паараметров
        $argCount = func_num_args();

        if ($argCount == 1)
        {
            $text = func_get_arg(0);

            if (isset(noxLocale::$words[$text]))
            {
                return noxLocale::$words[$text];
            } else
            {
                return $text;
            }
        } else
        {
            $value = func_get_arg($argCount-1);
            $value =($value%10==1 && $value%100!=11 ? 0 : $value%10>=2 && $value%10<=4 &&
                ($value%100<10 || $value%100>=20) ? 1 : 2);

            if (isset(noxLocale::$words[$value]))
            {
                return noxLocale::$words[$value];
            } else
            {
                return func_get_arg($value);
            }
        }
    }
}

/**
 * Возвращает перевод фразы
 *
 * Применение:
 * get('Hello') = Здравствуйте
 * get('%d минута', '%d минуты', '%d минуты', 51) - %d минута
 *
 * @return string Фраза на текущем языке
 */
function _t()
{
    $params = func_get_args();
    return call_user_func_array('noxLocale::get', $params);
}

?>