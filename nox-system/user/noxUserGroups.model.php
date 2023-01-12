<?php
/**
 * noxUserGroupsModel
 *
 * Модель связи пользователей и групп
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage user
 */

class noxUserGroupsModel extends noxModel
{
    /**
     * Таблица модели
     * @var string
     */
    var $table = 'nox_user_groups';

    /**
     * Возвращает массив групп, в которых состоит пользователь
     * @param $user_id
     * @return array
     */
    public function getByUser($user_id)
    {
        if (!$user_id)
        {
            return false;
        }
        return $this->select('group_id')->where('user_id', $user_id)->fetchAll('group_id', 'group_id');
    }

    public function getByUsers(Array $user_id)
    {
        if (!$user_id)
        {
            return false;
        }
        return $this->reset()->where('user_id', $user_id)->fetchAll('user_id', 'group_id', true);
    }
}