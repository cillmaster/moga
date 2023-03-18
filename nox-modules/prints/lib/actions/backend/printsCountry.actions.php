<?php

class printsCountryActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsCountryModel
     */
    public $model;

    public $caption = 'Страны';

    public function execute()
    {
        if(!$this->haveRight('category')) {
            return 401;
        }
        $this->model = new printsCountryModel();
        return parent::execute();
    }

    public function actionDefault()
    {
        $this->addVar('res', $this->model->getAll());
    }

    /**
     * Добавление
     */
    public function actionAdd()
    {
        $id = getParam(@$_GET['id'], 0);

        //Сохраняем
        if (isset($_POST['submit']) && isset($_POST['new'])) {
            $new = $_POST['new'];
            $this->model->insert($new);
            noxSystem::location('?section=country');
        }

        $this->caption = 'Добавление страны';

        if ($id) {
            $ar = $this->model->getById($id);
        } else {
            $ar = $this->model->getEmptyFields();
        }
        $this->addVar('ar', $ar);

        $this->templateFileName = $this->moduleFolder . '/templates/backend/countryEdit.html';
    }

    /**
     * Редактирование
     */
    public function actionEdit()
    {
        $id = getParam(@$_GET['id'], 0);

        if (!$id) {
            noxSystem::location('?section=country');
        }

        //Сохраняем
        if (isset($_POST['submit']) && isset($_POST['new'])) {
            $new = $_POST['new'];
            $this->model->updateById($id, $new);
            noxSystem::location('?section=country');
        }

        $this->caption = 'Редактирование страны';

        $ar = $this->model->getById($id);
        $this->addVar('ar', $ar);
    }

    /*
	 * Удаление
	 */
    public function actionDelete()
    {
        $id = getParam(@$_GET['id']);
        if (!$id) {
            return 400;
        }

        $this->model->deleteById($id);
        if ($this->ajax()) {
            return 200;
        } else {
            noxSystem::location('?section=country');
        }
    }
}
