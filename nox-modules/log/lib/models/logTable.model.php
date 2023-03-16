<?php
/**
 * Модель лога
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    log
 */

class logTableModel extends noxModel
{
    var $table = 'log_table';

    /**
     * Добавляет запись в лог
     * @param $type Тип
     * @param $action Действие
     * @param $itemId Над чем
     */
    public function log($type, $action, $itemId)
    {
        $this->insert(
            array(
                'user_id' => noxSystem::getUserId(),
                'type' => $type,
                'action' => $action,
                'item_id' => $itemId,
                'date' => noxDate::toSql(),
            )
        );
    }
}
?>