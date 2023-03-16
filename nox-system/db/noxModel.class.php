<?php

use nox\helpers\PaginatorDatasource;
/**
 * noxModel
 *
 * Базовый класс модели
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage db
 */

class noxModel extends noxDbQuery implements PaginatorDatasource
{
    /**
     * Таблица модели
     * @var string
     */
    public $table = '';

    /**
     * Имя поля с id записи
     *
     * @var string
     */
    public $id_field = 'id';

    /**
     * Поля в таблице
     *
     */
    public $fields = array();

    /**
     * Поля для выборки
     */
    protected $what = '*';

    /**
     * Условия отбора
     *
     */
    protected $where = '';

    /**
     * Группировка
     */
    protected $group = '';

    /**
     * Порядок выборки
     */
    protected $order = '';

    /**
     * Ограничение выборки
     */
    protected $limit = false;

    /**
     * Конструктор
     *
     * @param mixed Либо название БД, либо объект соединения с БД
     * @param string Имя таблицы
     */
    public function __construct($db = false, $table = false)
    {
        parent::__construct($db);
        //Получаем описание таблицы
        if ($table)
        {
            $this->table = $table;
        }
        $this->fields = $this->db->scheme($this->table);
        $this->select();
    }

    /**
     * Возвращает запрос к БД.
     *
     * @return bool
     */
    public function getSql()
    {
        $sql = 'SELECT ' . $this->what . ' FROM ' . $this->table . $this->where;

        if ($this->group){
            $sql .= ' GROUP BY ' . $this->group;
        }
        if ($this->order)
        {
            $sql .= ' ORDER BY ' . $this->order;
        }
        if ($this->limit)
        {
            $sql .= ' LIMIT ' . $this->limit[0] . ', ' . $this->limit[1];
        }
        return $sql;
    }

    /**
     * Приводит значение поля к типу данного поля в таблице
     * @param $field string Имя поля
     * @param $value mixed Значение
     * @return mixed
     * @throws noxException
     */
    public function castFieldValue($field, $value)
    {
        //_d($field, $value);
        if (isset($this->fields[$field]))
        {
            return
                $this->db->castValue($value,
                    $this->fields[$field]['type'],
                    $this->fields[$field]['null']);
        }
        else
        {
            //return false;
            if (noxConfig::isDebug())
            {
                throw new noxException('Неизвестное поле (`' . $field . '`) в массиве!');
            } else
            {
                return false;
            }
        }
    }

    /**
     * Приводит массив со значениями полей к типу данных полей в таблице
     * @param $array
     * @return array
     * @throws noxException
     */
    public function castArray(&$array)
    {
        foreach ($array as $key => $value)
        {
            if (isset($this->fields[$key]))
            {
                //Приводим к нужному типу значение
                $array[$key] = $this->db->castValue($value,
                    $this->fields[$key]['type'],
                    $this->fields[$key]['null']);
            } else
            {
                unset($value);
                unset($array[$key]);

                if (noxConfig::isDebug())
                {
                    throw new noxException('Неизвестное поле в массиве!');
                }
            }
        }
        return $array;
    }

    /**
     * Сбрасывает параметры запроса
     * @return noxModel
     */
    public function reset()
    {
        return $this->select()->where()->order()->group()->limit();
    }

    /**
     * Задает поля для выборки
     *
     * @param string названия полей через запятую
     * @return noxModel
     */
    public function select($fields = '*')
    {
        if ($fields == '*')
        {
            $this->what = '`' . implode('`, `', array_keys($this->fields)) . '`';
        } else
        {
            if (is_array($fields))
            {
                $this->what = '`' . implode('`, `', $fields) . '`';
            } else
            {
                $this->what = $fields;
            }
        }
        $this->exec = false;
        return $this;
    }

    /**
     * Задает условия для выборки
     *
     * Несколько вариантов записи:
     *    1)    $sql
     *    2)    ($field1 => $value1, $field2 => $value2)
     *    3)    $field,        array($value1, $value2, ...)
     *    4)    $field,        $value
     *
     * @param mixed $param1
     * @param mixed $param2
     * @return noxModel
     */
    public function where($param1 = false, $param2 = false)
    {
        $where = ' WHERE ';
        if (!$param1 && !$param2)
        {
            //Очистить условия
            $where = '';
        } elseif ($param1 && is_string($param1) && !$param2)
        {
            //1) Задан SQL
            if(!is_null($param2)) {
                $where .= $param1;
            }
            //1.1) Задан NULL
            else {
                $where .= $param1 . ' IS NULL';
            }
        } elseif (is_array($param1) && !$param2)
        {
            //2) Массив с условиями
            foreach ($param1 as $k => $v)
            {
                if (is_array($v))
                {
                    if (isset($v['begin']) || isset($v['end']))
                    {
                        if (isset($v['begin']) && isset($v['end']))
                        {
                            $where .= '(`' . $k . '` BETWEEN ' .
                                $this->castFieldValue($k, @$v['begin']) . ' AND ' .
                                $this->castFieldValue($k, @$v['end']) . ') AND ';
                        } elseif (isset($v['begin']))
                        {
                            $where .= '(`' . $k . '` >= ' .
                                $this->castFieldValue($k, @$v['begin']) . ') AND ';
                        } elseif (isset($v['end']))
                        {
                            $where .= '(`' . $k . '` <= ' .
                                $this->castFieldValue($k, @$v['end']) . ') AND ';
                        }
                    } else
                    {
                        $where .= '`' . $k . '` IN(';
                        if ($v)
                        foreach ($v as $v2)
                        {
                            $where .= $this->castFieldValue($k, $v2) . ',';
                        } else
                        {
                            $where .= '0,';
                        }
                        $where = substr($where, 0, -1) . ') AND ';
                    }
                } else
                {
                    if(is_null($v)) {
                        $where .= '`' . $k . '` IS ' . $this->castFieldValue($k, $v) . ' AND ';
                    }
                    else {
                        $where .= '`' . $k . '`=' . $this->castFieldValue($k, $v) . ' AND ';
                    }
                }
            }
            $where = substr($where, 0, -5);
        } elseif (is_array($param2))
        {
            //3) Несколько значений для одного поля

            if (isset($param2['begin']) || isset($param2['end']))
            {
                if (@$param2['begin'] && @$param2['end'])
                {
                    $where .= '(`' . $param1 . '` BETWEEN ' .
                        $this->castFieldValue($param1, $param2['begin']) . ' AND ' .
                        $this->castFieldValue($param1, $param2['end']) . ')';
                } elseif (@$param2['begin'])
                {
                    $where .= '(`' . $param1 . '` >= ' .
                        $this->castFieldValue($param1, $param2['begin']) . ')';
                } elseif (@$param2['end'])
                {
                    $where .= '(`' . $param1 . '` <= ' .
                        $this->castFieldValue($param1, $param2['end']) . ')';
                }
            } else
            {
                $where .= '`' . $param1 . '` IN(';
                if ($param2)
                foreach ($param2 as $v)
                {
                    $where .= $this->castFieldValue($param1, $v) . ',';
                } else
                {
                    $where .= '0,';
                }
                $where = substr($where, 0, -1) . ') ';
            }
        } else
        {
            $where .= '`' . $param1 . '`=' . $this->castFieldValue($param1, $param2);
        }
        $this->where = $where;
        $this->exec = false;
        return $this;
    }

    /**
     * Задает группировку
     *
     * @param string выражение в формате SQL или название поля
     * @return noxModel
     */
    public function group($group = ''){
        $this->group = $group;
        $this->exec = false;
        return $this;
    }

    /**
     * Задает сортировку
     *
     * @param string выражение в формате SQL или название поля
     * @return noxModel
     */
    public function order($order = '')
    {
        $this->order = $order;
        $this->exec = false;
        return $this;
    }

    /**
     * Задает ограничения по количеству строк
     *
     * @param int начальная строка выборки
     * @param int количество строк
     * @return noxModel
     */
    public function limit($start = 0, $count = 0)
    {
        if (!$start && !$count)
        {
            $this->limit = false;
        } elseif ($start && !$count)
        {
            $this->limit = array(0, intval($start));
        } else
        {
            $this->limit = array(intval($start), intval($count));
        }
        $this->exec = false;
        return $this;
    }

    /**
     * Возвращает первую или одну запись в соответствии с условием
     *
     * Несколько вариантов записи:
     *    1)    $sql
     *    2)    ($field1 => $value1, $field2 => $value2)
     *    3)    $field,        array($value1, $value2, ...)
     *    4)    $field,        $value
     *
     * @param mixed $param1
     * @param mixed $param2
     * @param bool все или только первая запись
     * @return noxModel
     */
    public function getByField($param1 = false, $param2 = false, $all = false)
    {
        //Применяем условия
        $this->where($param1, $param2);
        if ($all)
        {
            return $this->fetchAll();
        } else
        {
            return $this->fetch();
        }
    }

    /**
     * Возвращает запись с соответсвующим значением ключа или массив строк, если значений несколько
     *
     * @param mixed $id Значение ID или массив значений
     * @return mixed
     */
    public function getById($id)
    {
        if (!$id)
        {
            return false;
        }
        $this->where($this->id_field, $id);
        if (is_array($id))
        {
            return $this->fetchAll();
        } else
        {
            return $this->fetch();
        }
    }

    /**
     * Возвращает определенное количество случайных записей. Функция делает запрос на каждую случайную запись. Опасно для производительности!
     *
     * @param int
     * @param array
     * @param mixed array bool
     * @return array
     */
    public function getRand($count = 1, $where = [], $ignoreIds = false)
    {
        if (!$count)
        {
            return false;
        }

        $where && $this->where($where);
        $ids = $this->select($this->id_field)->fetchAll($this->id_field, $this->id_field);

        if(is_array($ignoreIds)) {
            foreach($ignoreIds as $kID) {
                unset($ids[$kID]);
            }
        }

        $this->select();
        if(sizeof($ids) > $count) {
            $ids = array_rand($ids, $count);
        }

        if($ids) {
            return $this->where('id', $ids)->fetchAll();
        }
        return [];
    }

    /**
     * Считает количество строк в запросе
     * @return int
     */
    public function count()
    {
        $count = false;
        if ($this->exec('SELECT COUNT(*) as `count` FROM ' . $this->table . $this->where))
        {
            //Получаем значение поля
            $count = $this->fetch('count');
        }
        $this->exec = false;
        return $count;
    }

    /**
     * Считает количество строк, удовлетворяющим с условиям
     *
     * Несколько вариантов записи:
     *    1)    $sql
     *    2)    ($field1 => $value1, $field2 => $value2)
     *    3)    $field,        array($value1, $value2, ...)
     *    4)    $field,        $value
     *
     * @param mixed $param1
     * @param mixed $param2
     * @param bool все или только первая запись
     * @return int
     */
    public function countByField($param1, $param2 = false)
    {
        //Применяем условия
        $this->where($param1, $param2);
        $count = false;
        if ($this->exec('SELECT COUNT(*) as `count` FROM ' . $this->table . $this->where))
        {
            //Получаем значение поля
            $count = $this->fetch('count');
        }
        $this->exec = false;
        return $count;
    }

    /**
     * Обновляет строки, удовлетворяющие с условиям
     *
     * Несколько вариантов записи:
     *    1)    $sql
     *    2)    ($field1 => $value1, $field2 => $value2)
     *    3)    $field,        array($value1, $value2, ...)
     *    4)    $field,        $value
     *
     * @param mixed $param1
     * @param mixed $param2
     * @param mixed $values Значения для обновления
     * @return noxModel
     */
    public function updateByField($param1, $param2 = false, $values)
    {
        //Применяем условия
        $this->where($param1, $param2);
        //Составляем запрос
        if (!is_array($values))
        {
            return false;
        }

        $str = '';
        foreach ($values as $k => $v)
        {
            $str .= '`' . $k . '`=' . $this->castFieldValue($k, $v) . ', ';
        }
        $str = substr($str, 0, -2);

        $res = $this->exec('UPDATE ' . $this->table . ' SET ' . $str . ' ' . $this->where);
        //_d('UPDATE ' . $this->table . ' SET ' . $str . ' ' . $this->where);
        $this->exec = false;
        return $res;
    }

    /**
     * Обновляет строку с соответсвующим значением ключа или массив строк, если значений id несколько
     *
     * @param mixed $id     записей, которые нужно обновить
     * @param array $values значения для обновления
     * @return noxModel
     */
    public function updateById($id, $values)
    {
        if (!$id)
        {
            return false;
        }
        return $this->updateByField($this->id_field, $id, $values);
    }


    /**
     * Удаляет строки, удовлетворяющие с условиям
     *
     * Несколько вариантов записи:
     *    1)    $sql
     *    2)    ($field1 => $value1, $field2 => $value2)
     *    3)    $field,        array($value1, $value2, ...)
     *    4)    $field,        $value
     *
     * @param mixed $param1
     * @param mixed $param2
     * @return noxModel
     */
    public function deleteByField($param1, $param2 = false)
    {
        //Применяем условия
        $this->where($param1, $param2);
        $res = $this->exec('DELETE FROM ' . $this->table . $this->where);
        $this->exec = false;
        return $res;
    }

    /**
     * Удаляет строку с соответсвующим значением ключа или массив строк, если значений несколько
     *
     * @param mixed
     * @return noxModel
     */
    public function deleteById($id)
    {
        if (!$id)
        {
            return false;
        }
        return $this->deleteByField($this->id_field, $id);
    }

    /**
     * Вставляет либо одну строку, либо несколько в таблицу выбранным методом
     *
     * @param array
     * @param bool метод вставки: false - INSERT, true - REPLACE
     * @return bool
     */
    public function insert($values, $replace = false)
    {
        if (!is_array($values))
        {
            return $this;
        }
        //Проверяем одна строка или несколько
        if (is_array(current($values)))
        {
            $values_array = $values;
        } else
        {
            $values_array[0] = $values;
        }
        $fields = array_keys(current($values_array));
        $fieldsCount = count($fields);
        $fields = '`' . implode('`, `', $fields) . '`';
        $values = '';
        //Проходим по массиву строк
        foreach ($values_array as $array)
        {
            if (count($array) != $fieldsCount)
            {
                //Если количество элементов не равно количеству полей, то пропускаем этот массив
                continue;
            }
            $values .= '(';
            $temp = '';
            //Проходим по столбцам
            foreach ($array as $k=> $v)
            {
                $temp .= $this->castFieldValue($k, $v) . ',';
            }
            $values .= substr($temp, 0, -1) . '), ';
        }
        $values = substr($values, 0, -2);
        //Выполняем запрос
        return $this->exec(($replace ? 'REPLACE' : 'INSERT') . ' INTO ' . $this->table . ' (' . $fields . ') VALUES ' . $values);
    }

    /**
     * Заменяет либо одну строку, либо несколько
     *
     * @param array
     * @return bool
     */
    public function replace($values)
    {
        return $this->insert($values, true);
    }

    /**
     * Возвращает массив со значениями полей по-умолчанию
     *
     * @return array
     */
    public function getEmptyFields()
    {
        $res = array();
        foreach ($this->fields as $key => $field)
        {
            $res[$key] = $field['default'];
        }
        return $res;
    }

    public function getItemsCount()
    {
        return $this->reset()->count();
    }

    public function getPageList($offset, $count)
    {
        return $this->reset()->limit($offset, $count)->fetchAll();
    }
}
