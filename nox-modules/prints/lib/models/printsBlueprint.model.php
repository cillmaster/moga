<?php
/**
 * Модель чертежа
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    prints
 */

class printsBlueprintModel extends noxModel
{
    public $table = 'prints_blueprint';

    /**
     * @return array
     */
    public function getAll()
    {
        return $this->order('name')->fetchAll('id');
    }

    /**
     * @return array
     */
    public function getList()
    {
        return $this->order('name')->fetchAll('id', 'name');
    }

    public function downloaded($id) {
        $this->exec('UPDATE ' . $this->table . ' SET downloads_count = downloads_count+1 WHERE id = ' . intval($id));
    }

}