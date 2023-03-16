<?php
    /**
     * Главное действие панели администратора
     *
     * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
     * @version    1.0
     * @package    car
     */

class printsClassesActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsCategoryModel
     */
    public $model;

    public function execute()
    {
        if(!$this->haveRight('category')) {
            return 401;
        }
        $this->model = new printsCategoryModel();
        return parent::execute();
    }

    public function actionDefault() {
        $this->title = 'Классы';

        $this->addVar('res', $this->model->fetchAll());
    }

    public function actionAdd() {
        if(isset($_POST['new'])) {
            $new = $_POST['new'];

            $model = new noxModel(false, 'nox_user');
            $tableName = strtolower('prints_class_' . $new['name']);
            $model->exec('SHOW TABLES LIKE "' . $tableName . '"');
            if($model->fetch()) {
                $this->addVar('error', 'Такая категория уже существует!');
            }
            else {
                $exec = $model->exec('CREATE TABLE `' . $tableName . '` (
                    id MEDIUMINT unsigned NOT NULL AUTO_INCREMENT,
                    PRIMARY KEY (`id`)
                ) ENGINE = InnoDB;');

                if($exec) {
                    $this->model->insert($new);
                    noxSystem::location('/administrator/prints/?section=classes');
                }
                else {
                    $this->addVar('error', 'Не могу создать таблицу `' . $tableName . '`. DB Error: ' . $model->db->error());
                }
            }

        }
        $this->title = 'Классы - добавление';
        $this->templateFileName = $this->moduleFolder . '/templates/backend/classesEdit.html';

    }

    public function actionEdit() {
        $id = (int)@$_GET['id'];
        if(!$id) {
            return 400;
        }

        if(isset($_POST['new'])) {
            $this->model->updateById($id, $_POST['new']);
            noxSystem::location('/administrator/prints/?section=classes');
        }

        $this->title = 'Классы - редактирование';

        $this->addVar('ar', $this->model->getById($id));

        $this->templateFileName = $this->moduleFolder . '/templates/backend/classesEdit.html';
    }

    public function actionDelete() {
        $id = (int)@$_GET['id'];
        if(!$id) {
            return 400;
        }

        $this->model->deleteById($id);

        if($this->ajax())
            return 200;
        else
            noxSystem::location('/administrator/prints/?section=classes');
    }
}