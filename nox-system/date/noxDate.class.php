<?php
/**
 * noxException
 *
 * Общий класс исключения.
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2.1
 * @package    nox-system
 * @subpackage date
 */

class noxDate
{
    /**
     * Временная зона по-умолчанию
     * @var string
     */
    public static $defaultTimezoneSet = 'Europe/Moscow';

    /**
     * Формат времени
     * @var string
     */
    public static $timeFormat = 'H:i'; //

    /**
     * Формат даты
     * @var string
     */
    public static $dateFormat = 'd.m.Y';

    /**
     * Формат Дата+Время
     * @var string
     */
    public static $dateTimeFormat = 'd.m.Y - H:i';

    /**
     * Формат Время+Дата
     * @var string
     */
    public static $timeDateFormat = 'H:i - d.m.Y';

    /**
     * Названия месяцев с января
     * @var array
     */
    public static $month = array('Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');

    /**
     * Названия дней недели с воскресенья
     * @var array
     */
    public static $dayOfWeek = array('Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота');

    /**
     * @static
     * Устанавливает временную зону
     *
     * @param string $timeZone Название временной зоны
     */
    public static function setTimeZone($timeZone)
    {
        self::$defaultTimezoneSet = $timeZone;
        //Устанавливаем временную зону
        date_default_timezone_set(noxDate::$defaultTimezoneSet);
    }

    /**
     * @static
     * Загружает настройки из конфигурацииы
     */
    public static function updateConfig()
    {
        $config = noxConfig::getConfig();
        if (!empty($config['timezoneSet'])) self::setTimeZone($config['timezoneSet']);
        if (!empty($config['timeFormat'])) self::$timeFormat = $config['timeFormat'];
        if (!empty($config['dateFormat'])) self::$dateFormat = $config['dateFormat'];
        if (!empty($config['dateTimeFormat'])) self::$dateTimeFormat = $config['dateTimeFormat'];
        if (!empty($config['timeDateFormat'])) self::$timeDateFormat = $config['timeDateFormat'];
    }

    /**
     * @static
     * Переводит время из времени по Гринвичу во время текущей временной зоны
     *
     * @param int $time timestamp
     * @return int timestamp
     */
    public static function fromGmt($time = 0)
    {
        static $offset = false;
        if ($offset === false) {
            $offset = intval(date('Z')) + intval(date('I')) * 3600;
        }
        if (!$time) {
            return time();
        } else
        {
            return $time + $offset;
        }
    }

    /**
     * @static
     * Переводит время из времени текущей временной зоны во время по Гринвичу
     *
     * @param int $time timestamp
     * @return int timestamp
     */
    public static function toGmt($time = 0)
    {
        static $offset = false;
        if ($offset === false) {
            $offset = intval(date('Z')) + intval(date('I')) * 3600;
        }
        if (!$time) {
            return time() - $offset;
        } else
        {
            return $time - $offset;
        }
    }

    /**
     * @static
     * Преобразует дату и время в формат MySQL
     *
     * @param $str Может быть либо числом, либо строкой формата 24.10.2011 12:33:32
     * @return string
     */
    public static function toSql($str = 0)
    {
        if (is_string($str)) {
            preg_match_all("/(\d+)/", $str, $time_arr);
            $ta = $time_arr[1];
            $time = mktime(@$ta[3], @$ta[4], @$ta[5], $ta[1], $ta[0], $ta[2]);
            //Если указано время, то переводим его в нулевой часовой пояс
            if (isset($ta[3])) {
                $time = self::toGmt($time);
            }
        } else
        {
            $time = self::toGmt(intval($str));
        }

        return date('Y-m-d H:i:s', $time);
    }

    /**
     * @static
     * Преобразует дату и время из формата MySQL в человеческий формат 24.10.2011 12:33:32
     *
     * @param $str        исходная строка или число
     * @param int $format вид возращаемого значения или формат для вывода
     * @return string
     */
    public static function toFormat($str = 0, $format = 0)
    {
        $time = self::fromGmt((is_string($str)) ? strtotime($str) : intval($str));

        if (is_int($format)) {
            switch ($format)
            {
                case 0: //date time
                    $format = self::$dateTimeFormat;
                    break;
                case 1: //time date
                    $format = self::$timeDateFormat;
                    break;
                case 2: //date
                    $format = self::$dateFormat;
                    break;
                case 3: //time
                    $format = self::$timeFormat;
                    break;
            }
            $res = date($format, $time);
        } else
        {
            $d = getdate($time);
            $format = str_replace('l', '\l', $format);
            $format = str_replace('F', '\F', $format);

            $res = date($format, $time);

            $res = str_replace('l', self::$dayOfWeek[$d['wday']], $res);
            $res = str_replace('F', self::$month[$d['mon'] - 1], $res);
        }
        return $res;
    }

    /**
     * @static
     * Преобразует дату и время из формата MySQL в человеческий формат времени 12:33:32
     *
     * @param $str        исходная строка или число
     * @return string
     */
    public static function toTime($str = 0)
    {
        return self::toFormat($str, 3);
    }

    /**
     * @static
     * Преобразует дату и время из формата MySQL в человеческий формат даты 24.10.2011
     *
     * @param $str        исходная строка или число
     * @return string
     */
    public static function toDate($str = 0)
    {
        return self::toFormat($str, 2);
    }

    /**
     * @static
     * Преобразует дату и время из формата MySQL в человеческий формат 24.10.2011 12:33:32
     *
     * @param $str        исходная строка или число
     * @return string
     */
    public static function toDateTime($str = 0)
    {
        return self::toFormat($str, 0);
    }

    /**
     * @static
     * Преобразует дату и время из формата MySQL в человеческий формат 24.10.2011 12:33:32
     *
     * @param $str        исходная строка или число
     * @return string
     */
    public static function toTimeDate($str = 0)
    {
        return self::toFormat($str, 1);
    }

    /**
     * @static
     * Преобразует дату и время из формата MySQL в человеческий формат 1 Января 2010, Понедельник
     *
     * @param $str исходная строка или число
     * @return string
     */
    public static function getStrDate($str = 0)
    {
        $time = self::fromGmt((is_string($str)) ? strtotime($str) : intval($str));
        $d = getdate($time);
        return ($d['mday'] . " " . self::$month[$d['mon'] - 1] . " " . $d['year'] . " года " . self::$dayOfWeek[$d['wday']]);
    }

    /**
     * @static
     * Возвращает строку вида 6 минут назад
     *
     * @param $str исходная строка или число
     * @return string
     */
    public static function getStrTime($str = 0)
    {
        $time = self::fromGmt((is_string($str)) ? strtotime($str) : intval($str));

        $now = self::fromGmt();
        //Разница времени
        $delta = $now - $time;

        if ($delta < 60) {
            $res = 'минуту назад';
        } elseif ($delta < 3600)
        {
            //Меньше часа
            $res = intval($delta / 60) . ' минут назад';
        } elseif ($delta < 3600 * 24)
        {
            //От часа до суток
            $h = intval($delta / 3600);
            $m = intval($delta / 60) - $h * 60;
            $res = $h . ' часов и ' . $m . ' минут назад';
        } else
        {
            $res = date(self::$timeDateFormat, $time);
        }
        return $res;
    }
}

?>