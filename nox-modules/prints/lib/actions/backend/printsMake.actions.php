<?php

class printsMakeActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsMakeModel
     */
    public $model;
    public $categoryModel;

    public $categories;

    public function execute()
    {
        if(!$this->haveRight('category')) {
            return 401;
        }

        $this->model = new printsMakeModel();
        $this->categoryModel = new printsCategoryModel();
        $this->categories = $this->categoryModel->getActiveAll();

        $this->addVar('categories', $this->categories);
        return parent::execute();
    }

    public function actionDefault() {
        $this->title = 'Производители';
        if(isset($_GET['filter']['category_id']) && $_GET['filter']['category_id']) {
            $this->model->where('class_id', $_GET['filter']['category_id']);
        }

        $page = $_GET['page'];
        $this->addVar('pager', (new kafPager())->create($this->model->count(), $onPage = 100, 5));

        $res = $this->model->limit(($page-1)*$onPage, $onPage)->order('name')->fetchAll();
        $this->addVar('res', $res);
    }

    public function actionAdd() {
        $this->title = 'Производитель - добавление';

        $form = new noxModelForm($this->model);

        $fname = $form->getFormInputName();
        if(isset($_POST[$fname])) {
            $new = $_POST[$fname];

            $category = (new printsCategoryModel())->getById(@$new['class_id']);
            if(!$category) {
                return 401;
            }

            $new['url'] = URLTools::string2url($new['name']);
            $this->model->insert($new);
            noxFileSystem::createFolder(noxRealPath('/blueprints/' . $category['url'] . '/' . $new['url']));
            noxFileSystem::createFolder(noxRealPath('/vectors/' . $category['url'] . '/' . $new['url'] . '/preview'));
            noxFileSystem::createFolder(noxRealPath('/vectors/' . $category['url'] . '/' . $new['url'] . '/store'));

            noxSystem::location('/administrator/prints/?section=make');
        }
        $form->acceptedFields('class_id,country_id,name,logo,web,full_name,address,description');
        if(isset($_GET['category_id'])) {
            $form->setValues(['class_id' => $_GET['category_id']]);
        }
        $form->addFieldsParams([
            'logo' => [
                'class' => 'file-editor',
                'data-folder' => '/nox-data/logos'
            ]
        ]);

        echo $form;
    }

    public function actionTop(){
        if(!$this->haveRight('control')){
            return 401;
        }
        if($id = (int)$_GET['id']) {
            $this->model->updateByField('id', $id, ['top' => (int)$_GET['status']]);
            exit;
        } else {
            return 400;
        }
    }

    public function actionEdit() {
        $id = @$_GET['id'];
        if(!$id) return 400;
        $this->title = 'Производитель - редактировнаие';

        $form = new noxModelForm($this->model);

        $fname = $form->getFormInputName();
        if(isset($_POST[$fname])) {
            $new = $_POST[$fname];

            $category = (new printsCategoryModel())->getById(@$new['class_id']);
            if(!$category) {
                return 401;
            }

            $new['url'] = URLTools::string2url($new['name']);
            $this->model->updateById($id, $_POST[$fname]);
            noxFileSystem::createFolder(noxRealPath('/blueprints/' . $category['url'] . '/' . $new['url']));
            noxFileSystem::createFolder(noxRealPath('/vectors/' . $category['url'] . '/' . $new['url'] . '/preview'));
            noxFileSystem::createFolder(noxRealPath('/vectors/' . $category['url'] . '/' . $new['url'] . '/store'));

            noxSystem::location('/administrator/prints/?section=make');
        }
        $form->setValues($this->model->getById($id));
        $form->acceptedFields('class_id,country_id,name,logo,web,full_name,address,description');
        $form->addFieldsParams([
            'logo' => [
                'class' => 'file-editor',
                'data-folder' => '/nox-data/logos'
            ]
        ]);

        echo $form;
    }
}