<?php
/**
 * Действие для изменения параметров страницы
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    pages
 * @subpackage admin
 */

class pagesAdministratorActions extends noxActions
{
    public $cache = false;

    public function actionPublish()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Получаем данные о странице
        $model = new pagesModel();
        $id = getParam(@$this->params['get']['id'], 0);
        $value = getParam(@$this->params['get']['value'], 'off');
        if ($model->updateById($id, array('published' => ($value == 'on' ? 3 : 0))))
        {
            return 200;
        }
        else
        {
            return 500;
        }
    }

    public function actionDelete()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Получаем данные о странице
        $model = new pagesModel();
        $id = getParam($this->params['get']['id'], 0);
        $ar = $model->select('url')->getById($id);

        if ($model->deleteById($id))
        {
            return 200;
        }
        else
        {
            return 500;
        }
    }
}

?>