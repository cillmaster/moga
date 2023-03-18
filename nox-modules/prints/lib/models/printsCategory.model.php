<?php
/**
 * Модель чертежа
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    car
 */

class printsCategoryModel extends noxModel
{
    public $table = 'prints_class';

    /**
     * Выбирает всё в виде списка id => name
     * @return array
     */
    public function getActiveList()
    {
        return $this->select('id, name')->where('`enabled` = 1')->fetchAll('id', 'name');
    }

    public function getActiveAll()
    {
        return $this->where('enabled', 1)->fetchAll('id');
    }

    public function getByUrl($url)
    {
        return $this->where(array('enabled' => 1, 'url' => $url))->fetch();
    }
}
