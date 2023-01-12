<?php
/**
 * noxDbMsAccessAdapter
 *
 * Адаптер для работы с базой данных MS Access
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage db
 */

class noxDbMsAccessAdapter extends noxDbAdapter
{
    /**
     * Переменная хранит идентификатор соединения
     *
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
            true;
        }

        //Соединяемся с помощью ODB
        $this->CID = odbc_connect('Driver=Microsoft Access Driver (*.mdb); DBQ=' . noxRealPath($params['db']) . ';', $params['login'], $params['password']);
        if (!($this->CID))
        {
            throw new noxException('Ошибка соединения с базой данных из файла "' . noxRealPath($params['db']) . '"!');
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
        if (!odbc_close($this->CID))
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
        return mb_convert_encoding(odbc_errormsg($this->CID), 'UTF-8', 'Windows-1251');
    }

    /**
     * Выполняет запрос к БД. Последний результат сохраняется в переменной объекта.
     *
     * @param string $sql запрос к БД
     */
    public function query($sql)
    {
        return @odbc_exec($this->CID, $sql);
    }

    /**
     * Освобождает ресурсы от запроса.
     *
     * @param $result результат запроса к БД.
     * @return bool
     */
    public function free($result)
    {
        return odbc_free_result($result);
    }

    /**
     * Количество строк в результате запроса.
     *
     * @param $result результат запроса к БД.
     * @return int
     */
    public function rows($result)
    {
        return odbc_num_rows($result);
    }

    /**
     * Возвращает 1 строку из результатов запроса в виде массива.
     *
     * @param $result  результат запроса к БД.
     * @param int $num номер строки в результатах
     * @return array Массив
     */
    public function fetch($result, $num = false)
    {
        if ($num)
        {
            return odbc_fetch_array($result, $num);
        } else
        {
            return odbc_fetch_array($result);
        }
    }

    /**
     * Возвращает ID последнего вставленной записи
     *
     * @return int
     */
    public function insertId()
    {
        $result = odbc_exec($this->CID, "select @@identity");
        $id = odbc_result($result, 1);
        odbc_free_result($result);
        return $id;
    }

    /**
     * Возвращает число затронутых прошлой операцией записей.
     *
     * @return int
     */
    public function affectedRows()
    {
        return odbc_num_rows($result);
    }

    /**
     * Можно ли использовать конструкцию LIMIT
     *
     * @return bool
     */
    public function canUseLimit()
    {
        return false;
    }
}

?>
