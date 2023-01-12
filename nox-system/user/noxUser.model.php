<?php
/**
 * noxUserModel
 *
 * Модель пользователя
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage user
 */

class noxUserModel extends noxModel
{
    /**
     * Таблица модели
     * @var string
     */
    var $table = 'nox_user';

    /**
     * Возвращает хеш для пароля
     * @static
     * @param string $str
     * @return string
     */
    public static function hash($str)
    {
        $temp = hash('SHA256', $str);
        return strtoupper(md5('md5'. $temp . substr($temp, 3, 18)));
    }

    /**
     * Генерирует пароль нужной длины
     *
     * @static
     * @param int $count
     * @return string
     */
    public static function generatePassword($count = 10)
    {
        $arr = array('a','b','c','d','e','f',
            'g','h','i','j','k','l',
            'm','n','o','p','r','s',
            't','u','v','x','y','z',
            'A','B','C','D','E','F',
            'G','H','I','J','K','L',
            'M','N','O','P','R','S',
            'T','U','V','X','Y','Z',
            '1','2','3','4','5','6',
            '7','8','9','0');
        // Генерируем пароль
        $pass = '';
        $c = count($arr);
        for($i = 0; $i < $count; $i++)
        {
            // Вычисляем случайный индекс массива
            $index = rand(0, $c - 1);
            $pass .= $arr[$index];
        }

        return $pass;
    }

    /**
     * Возвращает пользователя по логину, проверяя так же и email
     * @param string $login
     * @return array
     */
    public function getByLogin($login)
    {
        $ar = $this->getByField('login', $login);
        if (!$ar)
        {
            $ar = $this->getByField('email', $login);
        }
        return $ar;
    }

    /**
     * Обновляет дату последнего логина
     * @param int $userId
     * @return bool|noxModel
     */
    public function updateLastVisit($userId = 0)
    {
        if (!$userId)
        {
            $userId = noxSystem::getUserId();
            if (!$userId)
            {
                return false;
            }
        }
        return $this->updateById($userId, array('last_visit_date' => noxDate::toSql()));
    }

    public function getList()
    {
        $this->select('id, full_name, login')->order('full_name, login');
        $res = array();
        while ($ar = $this->fetch())
        {
            $res[$ar['id']] = !empty($ar['full_name']) ? $ar['full_name'] : $ar['login'];
        }
        return $res;
    }
}

?>