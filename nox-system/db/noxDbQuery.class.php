<?php
/**
 * noxDbQuery
 *
 * Класс для работы с запросами
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage db
 */

class noxDbQuery
{
    /**
     * Переменная хранит идентификатор соединения
     *
     * @var noxDbAdapter
     */
    public $db = null;

    /**
     * Переменная хранит результат последнего запроса
     *
     */
    public $result = false;

    /**
     * Запрос выполнен и его выполнение не требуется?
     *
     * @var bool
     */
    public $exec = false;

    /**
     * Количество строк в выборке
     *
     * @var int
     */
    protected $rows = -1;

    /**
     * Конструктор
     *
     * @param mixed Либо название БД, либо объект соединения с БД
     */
    public function __construct($db = null)
    {
        if ($db)
        {
            if (is_string($db))
            {
                $this->db = noxDbConnector::getConnection($db);
            } else
            {
                $this->db = $db;
            }
        } else
        {
            $this->db = noxDbConnector::getConnection();
        }
    }

    /**
     * Возвращает запрос к БД.
     *
     * @return string
     */
    public function getSql()
    {
        return false;
    }

    /**
     * Экранирует строку
     * @param $string
     * @return string
     */
    public function escape($string)
    {
        return $this->db->escape($string);
    }

    /**
     * Выполняет запросы, разделенные ;
     * @param $text
     * @return bool
     */
    public function execMultiQuery($text)
    {
        $query = array_filter(explode(";", $text));
        if ($query)
        {
            foreach ($query as $q)
            {
                $q = trim($q);
                if (!$q)
                {
                    continue;
                }
                if (!$this->exec($q))
                {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Выполняет запрос к БД и возвращает true в случае успеха.
     *
     * @param string запрос к БД на языке SQL
     * @return bool
     */
    public function exec($sql = '')
    {
        $this->exec = true;
        $this->rows = -1;

        //Получаем запрос, если его не ввели
        if (!$sql)
        {
            $sql = $this->getSql();
        }
        if (!$sql)
        {
            throw new noxException('Отсутствует SQL запрос!');
        }
        //Проверяем БД
        if (!$this->db)
        {
            throw new noxException('Ошибка соединения с БД!');
        }

        //Статистика. Считаем запросы
        if(!isset($GLOBALS['cron'])){
            $GLOBALS['statistic']['dbQueries']++;
            $GLOBALS['statistic']['dbQueriesSQL'][] = $sql;
            noxSystem::$console->log($sql);
        }

        //Выполняем запрос
        if ($this->result = $this->db->query($sql))
        {
            return true;
        } else
        {
            if (noxConfig::isDebug() && !isset($GLOBALS['cron']))
            {
                throw new noxException('Ошибка при запросе к БД!<br /><br />SQL: ' . htmlspecialchars($sql) . '<br /><br />Ошибка: ' . $this->db->error());
            }
            return false;
        }
    }

    /**
     * Возвращает 1 строку из результатов запроса в виде массива или, если задано имя поля, значение поля из строки.
     *
     * @param string $field имя поля или false
     * @return array Массив
     */
    public function fetch($field = '')
    {
        if (!$this->exec)
        {
            $this->exec();
        }
        if ($this->result)
        {
            if ($ar = $this->db->fetch($this->result))
            {
                if ($field)
                {
                    return $ar[$field];
                } else
                {
                    return $ar;
                }
            }
        }
        return false;
    }

    /**
     * Возвращает массив из всех строк результатов запроса, используя field в качестве ключа массива
     *
     * @param string $key_field   имя поля для ключа
     * @param string $value_field имя поля для значения
     * @param bool $array         если true, то по ключу будет создаваться массив
     * @return array Массив
     */
    public function fetchAll($key_field = '', $value_field = '', $array = false)
    {
        if (!$this->exec)
        {
            $this->exec();
        }
        if ($this->result)
        {
            $result = array();

            if (!$key_field && !$value_field)
            {
                //Если не ключ, не поле
                while ($ar = $this->db->fetch($this->result))
                {
                    $result[] = $ar;
                }
            } elseif ($key_field && $value_field && $array)
            {
                //Если ключ, поле, массив
                while ($ar = $this->db->fetch($this->result))
                {
                    $result[$ar[$key_field]][] = $ar[$value_field];
                }
            } elseif ($key_field && $value_field && !$array)
            {
                //Если ключ, поле, не массив
                while ($ar = $this->db->fetch($this->result))
                {
                    $result[$ar[$key_field]] = $ar[$value_field];
                }
            } elseif ($key_field && !$value_field && $array)
            {
                //Если ключ, не поле, массив
                while ($ar = $this->db->fetch($this->result))
                {
                    $result[$ar[$key_field]][] = $ar;
                }
            } elseif ($key_field && !$value_field && !$array)
            {
                //Если ключ, не поле, не массив
                while ($ar = $this->db->fetch($this->result))
                {
                    $result[$ar[$key_field]] = $ar;
                }
            } elseif (!$key_field && $value_field)
            {
                //Если не ключ, поле
                while ($ar = $this->db->fetch($this->result))
                {
                    $result[] = $ar[$value_field];
                }
            }

            return $result;
        }
        else
        {
            return false;
        }
    }


    /**
     * Возвращает количество строк в результате запроса
     *
     * @return array Массив
     */
    public function rows()
    {
        if (!$this->exec)
        {
            $this->exec();
        }

        if ($this->rows < 0)
        {
            //Пробуем определить функцией БД
            $this->rows = $this->db->rows($this->result);
        }
        return $this->rows;
    }

    /**
     * Возвращает ID последнего вставленного элемента
     *
     * @return int id
     */
    public function insertId()
    {
        return $this->db->insertId();
    }

    /**
     * Возвращает количество строк удаленных запросом
     *
     * @return array Массив
     */
    public function affectedRows()
    {
        return $this->db->affectedRows();
    }
}

?>