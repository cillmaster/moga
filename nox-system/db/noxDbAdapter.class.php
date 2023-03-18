<?php
/**
 * noxDbAdapter
 *
 * Абстрактный адаптер для работы с базой данных
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage db
 */

abstract class noxDbAdapter
{
    /**
     * Выполняет соединение с БД.
     *
     * @param array $params Параметры соединения
     * @return bool
     */
    abstract function connect($params);

    /**
     * Закрывает соединение с БД.
     *
     * @return bool
     */
    abstract function close();

    /**
     * Возвращает последнюю ошибку
     *
     * @return string Строка ошибки
     */
    abstract function error();

    /**
     * Выполняет запрос к БД. Последний результат сохраняется в переменной объекта.
     *
     * @param string $sql Запрос к БД
     * @return bool
     */
    abstract function query($sql);

    /**
     * Освобождает ресурсы от запроса.
     *
     * @param mixed $result Результат запроса к БД.
     * @return bool
     */
    abstract function free($result);

    /**
     * Количество строк в результате запроса.
     *
     * @param mixed $result Результат запроса к БД.
     * @return int Количество строк.
     */
    abstract function rows($result);

    /**
     * Возвращает 1 строку из результатов запроса в виде ассоциативного массива.
     *
     * @param mixed $result результат запроса к БД.
     * @param int $num      номер строки в результатах
     * @return array
     */
    abstract function fetch($result, $num = 0);

    /**
     * Экранирует строку для использования в запросах в качестве значений.
     *
     * @param string $string Исходная строка
     * @return string
     */
    public function escape($string)
    {
        //Если включены magic_quotes, ничего не делаем
        if (!get_magic_quotes_gpc())
        {
            $string = addslashes($string);
        }
        return trim($string);
    }

    /**
     * Приводит значение к нужному типу
     *
     * @param mixed $value
     * @param string $type
     * @param bool $is_null
     * @return mixed
     */
    public function castValue($value, $type, $is_null = false)
    {
        if($is_null && $value === NULL) return 'NULL';
        switch ($type)
        {
            case 'bool':
            case 'int':
            case 'tinyint':
                return (int)$value;
            case 'double':
            case 'float':
                return str_replace(',', '.', (double)$value);
            case 'date':
                if (!$value)
                {
                    if ($is_null)
                    {
                        return 'NULL';
                    } else
                    {
                        return "'0000-00-00'";
                    }
                }
            case 'datetime':
                if (!$value)
                {
                    if ($is_null)
                    {
                        return 'NULL';
                    } else
                    {
                        return "'0000-00-00 00:00:00'";
                    }
                }
            case 'varchar':
            case 'text':
            default:
                return "'" . $this->escape($value) . "'";
        }
    }

    /**
     * Возвращает ID последнего вставленной записи
     *
     * @return int
     */
    abstract public function insertId();

    /**
     * Возвращает число затронутых прошлой операцией записей.
     *
     * @return int
     */
    abstract public function affectedRows();

    /**
     * Можно ли использовать конструкцию LIMIT
     *
     * @return bool
     */
    public function canUseLimit()
    {
        return true;
    }

    /**
     * Возвращает описание таблицы
     * @abstract
     * @param string $table
     * @return array
     */
    abstract public function scheme($table);
}

?>