<?php
/**
 * Действия для управления группами
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    users
 * @subpackage admin
 */

class usersGroupsActions extends noxThemeActions
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Группы пользователей';

    /**
     * Действие по-умолчанию
     *
     * @return int
     */
    public function actionDefault()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('groups'))
        {
            return 401;
        }

        //Модель страниц
        $model = new noxGroupModel();
        //Получаем все страницы
        $res = $model->order('name')->fetchAll();

        //Добавляем переменные
        $this->addVar('res', $res);
    }

    /**
     * Действие добавления группы
     *
     * @return int
     */
    public function actionAdd()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('groups'))
        {
            return 401;
        }

        $this->caption = 'Добавить группу пользователей';

        //Модель страниц
        $model = new noxGroupModel();
        $rightsModel = new noxGroupRightsModel();

        //Если данные пришли
        if (isset($_POST['new']))
        {
            if ($model->insert($_POST['new']))
            {
                $id = $model->insertId();
                //Занимаемся правами
                foreach ($_POST['rights'] as $module => $array)
                {
                    foreach ($array as $right => $val)
                    {
                        if ($val)
                        {
                            $rightsModel->addRight($module, $right, $id);
                        } else
                        {
                            $rightsModel->deleteRight($module, $right, $id);
                        }
                    }
                }
                //Если форма сохранилась
                noxSystem::location('?section=groups');
            }

            $ar = $_POST['new'];
        }
        else
        {
            //Задан ли параметр для копирования
            $id = getParam(@$this->params['get']['id'], 0);
            if ($id)
            {
                $ar = $model->getById($id);
            } else
            {
                $ar = $model->getEmptyFields();
            }
        }

        $this->templateFileName = $this->moduleFolder . '/templates/groupsEdit.html';
        $this->addVar('ar', $ar);

        //получаем список прав из описаний модулей
        $this->addVar('modules', noxConfig::getModules());
        //Получаем текущие
        $rights = array();
        if ($res = $rightsModel->getByGroup($ar['id']))
        {
            foreach ($res as $ar)
            {
                $rights[$ar['module']][$ar['right']] = true;
            }
        }
        $this->addVar('rights', $rights);
    }

    /**
     * Действие редактирования
     *
     * @return int
     */
    public function actionEdit()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('groups'))
        {
            return 401;
        }

        $this->caption = 'Редактировать группу пользователей';

        //Модель страниц
        $model = new noxGroupModel();
        $rightsModel = new noxGroupRightsModel();

        //Если данные пришли
        if (isset($_POST['new']))
        {
            $id = intval($_POST['id']);
            if ($model->updateById($id, $_POST['new']))
            {
                //Занимаемся правами
                foreach ($_POST['rights'] as $module => $array)
                {
                    foreach ($array as $right => $val)
                    {
                        if ($val)
                        {
                            $rightsModel->addRight($module, $right, $id);
                        } else
                        {
                            $rightsModel->deleteRight($module, $right, $id);
                        }
                    }
                }
                //Если форма сохранилась
                noxSystem::location('?section=groups');
            }

            $ar = $_POST['new'];
        }
        else
        {
            //Задан ли параметр для копирования
            $id = getParam(@$this->params['get']['id'], 0);
            if ($id)
            {
                $ar = $model->getById($id);
            } else
            {
                return 400;
            }
        }

        //$this->template->loadFromFile($this->moduleFolder . '/templates/groupsEdit.html');
        $this->addVar('ar', $ar);

        //получаем список прав из описаний модулей
        $this->addVar('modules', noxConfig::getModules());
        //Получаем текущие
        $rights = array();
        if ($res = $rightsModel->getByGroup($ar['id']))
        {
            foreach ($res as $ar)
            {
                $rights[$ar['module']][$ar['right']] = true;
            }
        }
        $this->addVar('rights', $rights);
    }

    /**
     * Действие удаления
     *
     * @return int
     */
    public function actionDelete()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('groups'))
        {
            return 401;
        }

        //Модель страниц
        $model = new noxGroupModel();

        $id = getParam(@$this->params['get']['id'], 0);
        if ($id)
        {
            $model->deleteById($id);
            return 200;
        } else
        {
            return 400;
        }
    }
}

?>