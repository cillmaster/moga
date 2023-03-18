<?php

class tagTagActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    public $model;

    public function execute()
    {
        if (!$this->haveRight('control')) {
            //return 401;
        }
        $this->templateFileName = $this->moduleFolder . '/templates/backend/editTags.html';

        return parent::execute();
    }

    public function actionCategory() {
        $id = $_GET['id'];
        $cm = new printsCategoryModel();
        $cItem = $cm->getById($id);
        $c = new tagCategoryModel();
        if(isset($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            $c->saveTags($tags, $id);
        }
        $this->addVar('tags', $c->getById($id));
        $this->title = $this->caption = 'Теги категории ' . $cItem['name'];
    }

    public function actionCategoryUse() {
        $id = $_GET['id'];
        $cm = new printsCategoryModel();
        $cItem = $cm->getById($id);
        $c = new tagCategoryUseModel();
        if(isset($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            $c->saveTags($tags, $id);
        }
        $this->addVar('tags', $c->getById($id));
        $this->title = $this->caption = 'Use - Теги категории ' . $cItem['name'];
    }

    public function actionMake() {
        $id = $_GET['id'];
        $cm = new printsMakeModel();
        $cItem = $cm->getById($id);
        $c = new tagMakeModel();
        if(isset($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            $c->saveTags($tags, $id);
        }
        $this->addVar('tags', $c->getById($id));
        $this->title = $this->caption = 'Теги производителя ' . $cItem['name'];
    }

    public function actionBlueprint() {
        $id = $_GET['id'];
        $cm = new printsBlueprintModel();
        $cItem = $cm->getById($id);
        $c = new tagBlueprintModel();
        if(isset($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            $c->saveTags($tags, $id);
        }
        $this->addVar('tags', $c->getById($id));
        $this->title = $this->caption = 'Теги чертежа ' . $cItem['name'];
    }

    public function actionVector() {
        $id = $_GET['id'];
        $cm = new printsVectorModel();
        $cItem = $cm->getById($id);
        $c = new tagVectorModel();
        if(isset($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            $c->saveTags($tags, $id);
        }
        $this->addVar('tags', $c->getById($id));
        $this->title = $this->caption = 'Теги вектора ' . $cItem['name'];
    }
}
