<?php
/**
 * Действие вывода блока логина
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    adv
 * @subpackage partners
 */

class usersDefaultLoginTemplateAction extends noxTemplateAction
{
    public $cache = false;

    public function loginBlock()
    {
        //Проверяем авторизацию пользователя
        $auth = noxSystem::$userControl->authorization();
        $this->addVar('auth', $auth);

        if ($auth)
        {
            //Получаем имя текущего пользователя
            $this->addVar('userId', noxSystem::$userControl->getUserId());
        }
    }
}

?>