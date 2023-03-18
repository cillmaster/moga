<?php
/**
 * Действие для отображения списка страниц
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    pages
 * @subpackage admin
 */

class pagesAdministratorAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Страницы сайта';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Модель страниц
        $model = new pagesModel();
        //Получаем все страницы
        $res = $model->order('url')->fetchAll();

        //Добавляем переменные
        $this->addVar('pages', $res);

        $this->addJs(noxSystem::$baseUrl . '/'.$this->moduleFolder . '/js/admin.js');
    }
}

?>