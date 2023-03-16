<?php
/**
 * Модель информации о закачке файла
 *
 * @author Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version 1.0
 * @package files
 */

class filesFileModel extends noxModel
{
	/**
	 * Таблица модели
	 *
	 */
	var $table='files_file';

    public function getAllActive()
    {
        return $this->select()->order('date DESC')->where('enabled', 1)->fetchAll('id');
    }

	public function getAll()
	{
		return $this->select()->order('date DESC')->where()->fetchAll('id');
	}
}

?>