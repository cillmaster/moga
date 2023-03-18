<?php
/**
 * noxSessionModel
 *
 * Модель cессии
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage user
 */

class noxSessionModel extends noxModel
{
    /**
     * Таблица модели
     * @var string
     */
    var $table = 'nox_session';

    /**
     * Возвращает хеш для пароля
     * @static
     * @param string $str
     * @return string
     */
    public static function hash($str)
    {
        return strtoupper(md5('md5'. $str));
    }

    /**
     * Сохраняем данные
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function saveData($id, $data)
    {
        if (is_array($data))
        {
            $data = serialize($data);
        }
        return $this->replace(
            array(
                'id' => $id,
                'time' => noxDate::toSql(),
                'data' => $data
            )
        );
    }

    /**
     * Возвращает данные сессии
     * @param string $id
     * @return string
     */
    public function getData($id)
    {
        $temp = $this->getById($id);
        if ($data = @unserialize($temp['data']))
        {
            return $data;
        } else
        {
            return $temp['data'];
        }
    }

    /**
     * Удаляет старые сессии, старше минимальным
     * @param int $minTime
     * @return bool
     */
    public function deleteOldData($minTime=0)
    {
        if (!$minTime)
        {
            $minTime = strtotime('-1 month');
        }
        return $this->deleteByField('time<"'.noxDate::toSql($minTime).'"');
    }

}

?>