<?php
/**
 * Действие для очистки кэша
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    system
 * @subpackage config
 */

class systemConfigClearcacheAction extends noxAction
{
    public $cache = false;

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        noxFileSystem::clearFolder(noxRealPath('nox-cache'));

        echo 'Кэш успешно очищен!';
    }
}

?>