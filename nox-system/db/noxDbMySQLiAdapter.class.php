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

class noxDbMySQLiAdapter extends noxDbAdapter
{
    /**
     * Переменная хранит идентификатор соединения
     * @var mysqli
     */
    private $CID;
    /**
     * @var string Имя БД
     */
    private $DB;

    public function __construct()
    {
        $this->CID = 0;
    }

    /**
     * Выполняет соединение с БД.
     *
     * @param array $params параметры соединения
     * @return bool
     */
    public function connect($params)
    {
        //Если соединение уже установлено, то закрываем его
        if ($this->CID)
        {
            $this->close();
        }

        $this->CID = mysqli_connect($params['host'], $params['login'], $params['password'], $params['db']);
        $this->DB = $params['db'];
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

        return true;
    }

    /**
     * Закрывает соединение с БД.
     *
     * @return bool
     */
    public function close()
    {
        if (!mysqli_close($this->CID))
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
        return mysqli_error($this->CID);
    }

    /**
     * Выполняет запрос к БД. Последний результат сохраняется в переменной объекта.
     *
     * @param string $sql запрос к БД
     * @return resource
     */
    public function query($sql)
    {
        return @mysqli_query($this->CID, $sql);
    }

    /**
     * Освобождает ресурсы от запроса.
     *
     * @param $result результат запроса к БД.
     * @return bool
     */
    public function free($result)
    {
        return mysqli_free_result($result);
    }

    /**
     * Количество строк в результате запроса.
     *
     * @param $result результат запроса к БД.
     * @return int Количество строк.
     */
    public function rows($result)
    {
        return mysqli_num_rows($result);
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
            mysqli_data_seek($result, $num);
        }
        return mysqli_fetch_assoc($result);
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
        return mysqli_real_escape_string($this->CID, trim($string));
    }

    /**
     * Возвращает ID последнего вставленной записи
     *
     * @return int
     */
    public function insertId()
    {
        return mysqli_insert_id($this->CID);
    }

    /**
     * Возвращает число затронутых прошлой операцией записей.
     *
     * @return int
     */
    public function affectedRows()
    {
        return mysqli_affected_rows($this->CID);
    }


    /**
     * Возвращает описание таблицы
     * @param string $table
     * @return array
     */
    public function scheme($table)
    {
        if (!noxConfig::isDebug())
        {
            //Пробуем прочитать из кеша
            if ($cache = noxSystemCache::get('MYSQLI-SCHEME', 3600))
            {
                if (isset($cache[$table]))
                {
                    return $cache[$table];
                }
            }
        }

        $res = mysqli_query($this->CID, "DESCRIBE " . $table);
        if (!$res)
        {
            throw new noxException('Не удалось получить описание таблицы ' . $table . '!');
        }
        $result = array();
        while ($row = mysqli_fetch_assoc($res))
        {
            $field = array();
            $field['attributes'] = FALSE;
            $i = strpos($row['Type'], '(');
            if ($i === FALSE)
            {
                $field['type'] = $row['Type'];
                $field['length'] = null;
            } else
            {
                $field['type'] = substr($row['Type'], 0, $i);
                $j = strpos($row['Type'], ')');
                if($j === (strlen($row['Type'])-1)) {
                    $field['length'] = substr($row['Type'], $i + 1, -1);
                }
                else {
                    $field['length'] = substr($row['Type'], $i + 1, $j-$i-1);
                    $field['attributes'] = strtolower(substr($row['Type'], $j + 2));
                }
            }
            $field['null'] = $row['Null'] == 'YES' ? TRUE : FALSE;
            $field['default'] = $row['Default'] === 'NULL' ? ($field['null'] ? null : $this->castValue('', $field['type'])) : $row['Default'];
            $field['extra'] = $row['Extra'];
            $field['key'] =  strtolower($row['Key']);
            $result[$row['Field']] = $field;
        }

        $res = mysqli_query($this->CID, 'SELECT * FROM `information_schema`.`KEY_COLUMN_USAGE` WHERE `TABLE_NAME` = "' . $table . '" AND `TABLE_SCHEMA` = "' . $this->DB . '" AND `REFERENCED_TABLE_NAME` IS NOT NULL');
        while ($row = mysqli_fetch_assoc($res))
        {
            $col = $row['COLUMN_NAME'];
            if(isset($result[$col]) && $row['CONSTRAINT_NAME'] !== 'PRIMARY')
                $result[$col]['ref'] = array(
                    'db' => $row['REFERENCED_TABLE_SCHEMA'],
                    'table' => $row['REFERENCED_TABLE_NAME'],
                    'column' => $row['REFERENCED_COLUMN_NAME'],
                );
        }

        if (!noxConfig::isDebug())
        {
            //Пробуем прочитать из кеша
            $cache = noxSystemCache::get('MYSQLI-SCHEME', 3600);
            $cache[$table] = $result;
            noxSystemCache::create('MYSQLI-SCHEME', $cache);
        }

        return $result;
    }
}

?>