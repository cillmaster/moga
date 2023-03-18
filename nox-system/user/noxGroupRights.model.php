<?php
/**
 * noxGroupRights
 *
 * Модель для хранения прав групп
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage user
 */

class noxGroupRightsModel extends noxModel
{
    /**
     * Таблица модели
     * @var string
     */
    var $table = 'nox_group_rights';

    /**
     * Возвращает права по Id пользователя
     *
     * @param $group_id
     * @return bool|noxModel
     */
    public function getByGroup($group_id)
    {
        if (!$group_id)
        {
            return false;
        }
        return $this->getByField('group_id', $group_id, true);
    }

    /**
     * Возвращает права по модулю
     * @param $module
     * @return bool|noxModel
     */
    public function getByModule($module)
    {
        if (!$module)
        {
            return false;
        }
        return $this->getByField('module', $module, true);
    }

    /**
     * Добавляет право
     * @param $module
     * @param $right
     * @param $group_id
     * @return bool
     */
    public function addRight($module, $right, $group_id)
    {
        return $this->replace(array('module'   => $module,
                                    'right'    => $right,
                                    'group_id' => $group_id));
    }

    /**
     * Проверяет права группы
     * @param $module
     * @param $right
     * @param $group_id
     * @return bool
     */
    public function haveRight($module, $right, $group_id)
    {
        return ($this->getByField(array('module'    => $module,
                                       'right'     => $right,
                                       'group_id'  => $group_id))
            ? true : false);
    }

    /**
     * Удаляет права
     * @param $module
     * @param $right
     * @param $group_id
     * @return bool
     */
    public function deleteRight($module, $right, $group_id)
    {
        return $this->deleteByField(array('module'   => $module,
                                          'right'    => $right,
                                          'group_id' => $group_id));
    }

    /**
     * Удаляет права по модулю
     * @param $module
     * @return bool
     */
    public function deleteByModule($module)
    {
        if (!$module)
        {
            return false;
        }
        return $this->deleteByField('module', $module);
    }
}

?>