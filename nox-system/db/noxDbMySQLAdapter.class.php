<?php
/**
 * noxDbMySQLAdapter
 *
 * MySQL адаптер для работы с базой данных
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage db
 */

class noxDbMySQLAdapter extends noxDbAdapter
{
    /**
     * Переменная хранит идентификатор соединения
     *
     * @var resource
     */
    private $CID;

    public function __construct()
    {
        $this->CID = 0;
    }

    /**
     * Выполняет соединение с БД.
     *
     * @param array $params Параметры соединения
     * @return bool
     */
    public function connect($params)
    {
        //Если соединение уже установлено, то закрываем его
        if ($this->CID)
        {
            return true;
        }

        //Открываем новое соединение с БД
        $this->CID = mysql_connect($params['host'], $params['login'], $params['password'], true);
        if (!($this->CID))
        {
            throw new noxException('Ошибка соединения с базой данных!');
        }

        if (!$this->query('SET NAMES utf8'))
        {
            throw new noxException($this->error());
        }

        if (!$this->query('SET `time_zone`=\'+00:00\''))
        {
            throw new noxException($this->error());
        }

        if (!mysql_select_db($params['db'], $this->CID))
        {
            throw new noxException($this->error());
        }

        return true;
    }

    /**
     * Закрывает соединение с БД.
     *
     * @return bool
     */
    public function close()
    {
        if (!mysql_close($this->CID))
        {
            throw new noxException($this->error());
        }
        $this->CID = null;
    }

    /**
     * Возвращает последнюю ошибку
     *
     * @return string
     */
    public function error()
    {
        return mysql_error($this->CID);
    }

    /**
     * Выполняет запрос к БД. Последний результат сохраняется в переменной объекта.
     *
     * @param string $sql запрос к БД
     * @return bool
     */
    public function query($sql)
    {
        return @mysql_query($sql, $this->CID);
    }

    /**
     * Освобождает ресурсы от запроса.
     *
     * @param $result результат запроса к БД.
     * @return bool
     */
    public function free($result)
    {
        return mysql_free_result($result);
    }

    /**
     * Количество строк в результате запроса.
     *
     * @param $result результат запроса к БД.
     * @return int
     */
    public function rows($result)
    {
        return mysql_num_rows($result);
    }

    /**
     * Возвращает 1 строку из результатов запроса в виде массива.
     *
     * @param $result  результат запроса к БД.
     * @param int $num номер строки в результатах
     * @return array
     */
    public function fetch($result, $num = 0)
    {
        if ($num)
        {
            mysql_data_seek($result, $num);
        }
        return mysql_fetch_assoc($result);
    }

    /**
     * Экранирует строку для использования в запросах в качестве значений.
     *
     * @param string $string исходная строка
     * @return string
     */
    public function escape($string)
    {
        //Если включены magic_quotes, ничего не делаем
        if (get_magic_quotes_gpc())
        {
            $string = stripcslashes($string);
        }
        return mysql_real_escape_string(trim($string), $this->CID);
    }

    /**
     * Возвращает ID последнего вставленной записи
     *
     * @return int
     */
    public function insertId()
    {
        return mysql_insert_id($this->CID);
    }

    /**
     * Возвращает число затронутых прошлой операцией записей.
     *
     * @return int
     */
    public function affectedRows()
    {
        return mysql_affected_rows($this->CID);
    }

    /**
     * Возвращает описание таблицы
     * @param string $table
     * @return array
     */
    public function scheme($table)
    {
        $res = mysql_query("DESCRIBE " . $table, $this->CID);
        if (!$res)
        {
            throw new noxException('Не удалось получить описание таблицы ' . $table . '!');
        }
        $result = array();
        while ($row = mysql_fetch_assoc($res))
        {
            $i = strpos($row['Type'], '(');
            if ($i === false)
            {
                $field['type'] = $row['Type'];
                $field['length'] = null;
            } else
            {
                $field['type'] = substr($row['Type'], 0, $i);
                $field['length'] = substr($row['Type'], $i + 1, -1);
            }
            $field['null'] = $row['Null'] == 'YES' ? 1 : 0;
            $field['default'] = $row['Default'] === 'NULL' ? ($field['null'] ? null : $this->castValue('', $field['type'])) : $row['Default'];
            $field['extra'] = $row['Extra'];
            $result[$row['Field']] = $field;
        }
        return $result;
    }

}

?>