<?php
/**
 * Действие для отображения добавления страницы
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    pages
 * @subpackage admin
 */

class pagesAdministratorAddAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Добавление страницы сайта';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Создем модель страницы
        $model = new pagesModel();

        //Если данные пришли
        if (isset($_POST['new']))
        {
            if ($model->insert($_POST['new']))
            {
                //Если форма сохранилась
                noxSystem::location(noxSystem::$moduleUrl);
            }
            $page = $_POST['new'];
        }
        else
        {
            //Задан ли параметр для копирования
            $id = getParam(@$this->params['get']['id'], 0);
            if ($id)
            {
                $page = $model->getById($id);
                $page['id'] = '';
            } else
            {
                $page = $model->getEmptyFields();
            }
        }

        $this->templateFileName = $this->moduleFolder . '/templates/administratorEdit.html';

        $this->addVar('page', $page);

        $this->addJs(noxSystem::$baseUrl . '/'.$this->moduleFolder . '/js/admin.js');
    }
}

?>