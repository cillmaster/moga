<?php
/**
 * Администрирование чертежей
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    car
 */

class printsBlueprintActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    /**
     * @var printsBlueprintModel
     */
    public $model;
    /**
     * @var printsCategoryModel
     */
    public $categoryModel;
    /**
     * @var printsMakeModel
     */
    public $makeModel;

    /**
     * @var array
     */
    public $categories;
    public $makes;

    public $caption = 'Чертежи';

    public function execute()
    {
        if (!$this->haveRight('blueprint')) {
            return 401;
        }

        $this->model = new printsBlueprintModel();
        $this->categoryModel = new printsCategoryModel();
        $this->makeModel = new printsMakeModel();

        $this->categories = $this->categoryModel->getActiveAll();
        //$this->makes = $this->makeModel->fetchAll('id');

        $this->addVar('categories', $this->categories);
        //$this->addVar('makes', $this->makes);
        return parent::execute();
    }

    public function actionDefault() {
        if(isset($_GET['filter']['category_id']) && isset($this->categories[$_GET['filter']['category_id']])) {
            $category = &$this->categories[$_GET['filter']['category_id']];
            $categoryDataTable = 'prints_class_' . $category['db_table'];
            $categoryDataModel = new noxModel(false, $categoryDataTable);

            if(isset($_GET['filter']['make_id']) && $_GET['filter']['make_id'] && isset($categoryDataModel->fields['make_id']) && is_numeric($_GET['filter']['make_id'])) {
                $where = 'class_id = ' .  $category['id'] . ' AND item_id IN(select id from `' . $categoryDataTable . '` where make_id = ' . $_GET['filter']['make_id'] . ')';
                $this->model->where($where);
            }
            else {
                $this->model->where('class_id', $category['id']);
            }
            $this->addVar('makes', $this->makeModel->getAllByCategory($category['id']));
        }

        if($_GET['search'] != '') {
            $this->model->where('`full_name` LIKE "%' . $this->model->escape($_GET['search']) . '%"');
        }

        $page = $_GET['page'];
        $this->addVar('pager', (new kafPager())->create($this->model->count(), $onPage = 100, 5));

        $res = $this->model->limit(($page-1)*$onPage, $onPage)->order('sort_name')->fetchAll('id');
        if(noxConfig::isProduction()) {
            foreach ($res as $row) {
                if (!file_exists(noxRealPath($row['filename']))) {
                    $res[$row['id']]['full_name'] .= '<span class="exist_error">no</span>';
                };
            }
        }
        $this->addVar('res', $res);
    }

    /**
     * Добавление
     */
    public function actionAdd()
    {
        //Сохраняем
        if (isset($_POST['submit']) && isset($_GET['category_id']) && isset($this->categories[$_GET['category_id']])) {
            $new = $_POST['new'];
            $categoryId = $_GET['category_id'];

            $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$categoryId]['db_table']);
            $form = new noxModelForm($categoryModel);

            $categoryData = $_POST[$form->getFormInputName()];
            unset($categoryData['id']);
            $categoryModel->insert($categoryData);
            $new['class_id'] = $categoryId;
            $new['item_id'] = $categoryModel->insertId();
            //Обновление информации о файле
            $new['update_date'] = noxDate::toSql();
            $new['views'] = isset($new['views']) ? Views::array2int($new['views']) : 0;
            $size = @getimagesize(noxRealPath($new['filename']));
            if ($size) {
                $new['resolution_width'] = $size[0];
                $new['resolution_height'] = $size[1];
                $new['ext'] = strtolower(substr(strrchr($new['filename'], '.'), 1));
            }
            $new['full_name'] = Prints::generateFullName($new, $categoryData);
            $new['sort_name'] = Prints::generateSortName($new, $categoryData);
            $new['url'] = Prints::generateSEOUrl($new['full_name']);

            $this->model->insert($new);
            $id = $this->model->insertId();


            if(!empty($_POST['relatedVectorId'])) {
                if((new printsVectorModel)->where('id', $_POST['relatedVectorId'])->count()) {
                    $insert = [
                        'blueprint_id' => $id,
                        'vector_id' => $_POST['relatedVectorId']
                    ];
                    (new printsRelationModel())->blueprintVectorRelationModel->replace($insert);
                }
            }

            //TODO: Пишем в лог

            //Пересчитываем количество чертежей марки
            if(isset($categoryData['make_id'])) {
                $itemsId = $categoryModel->select('id')->where('make_id', $categoryData['make_id'])->fetchAll(false, 'id');
                if(!$itemsId) {
                    $count = 0;
                }
                else {
                    $count = $this->model->reset()->where(['class_id' => $categoryId, 'item_id' => $itemsId])->count();
                }
                $this->makeModel->updateById($categoryData['make_id'], array('blueprints_count' => $count));
            }
            noxSystem::location('/administrator/prints/?section=blueprint&filter[category_id]=' . $categoryId
                . '&filter[make_id]=' . $categoryData['make_id']);
        }

        $this->caption = 'Добавление чертежа';

        if(isset($_GET['category_id']) && isset($this->categories[$_GET['category_id']])) {
            $categoryId = $_GET['category_id'];
            $this->addVar('step', 2);
            $this->addVar('category', $this->categories[$categoryId]);

            $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$categoryId]['db_table']);
            $form = new noxModelForm($categoryModel);
            $form->onlyFields = true;
            $form->setValues(['make_id' => @$_GET['make_id']]);
            if(isset($form->model->fields['make_id'])) {
                $form->model->fields['make_id']['sql_where'] = 'class_id = ' . $categoryId;
                $form->addFieldsParams(['make_id' => ['class' => 'js-change-data-folder']]);
            }
            $this->addVar('f', $form);
            $this->addVar('makeUrls', $this->makeModel->reset()->where('class_id', $categoryId)->fetchAll('id', 'url'));
        }
        else {
            $this->addVar('step', 1);
            $this->addVar('cats', $this->categoryModel->getActiveList());
        }
    }

    public function actionEdit() {
        $id = @$_GET['id'];

        $ar = $this->model->getById($id);
        if(!$ar) return 401;

        $categoryId = $ar['class_id'];
        $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$categoryId]['db_table']);
        $form = new noxModelForm($categoryModel);
        $printsRelationModel = new printsRelationModel();
        $relatedVectorId = $printsRelationModel->blueprintVectorRelationModel->where('blueprint_id', $id)->fetch('vector_id');
        $relatedVectorId && $this->addVar('relatedVectorId', $relatedVectorId);

        if (isset($_POST['submit'])) {
            $new = $_POST['new'];

            $categoryData = $_POST[$form->getFormInputName()];

            unset($categoryData['id']);
            $categoryModel->updateById($ar['item_id'], $categoryData);
            $new['class_id'] = $categoryId;
            //Обновление информации о файле
            $new['update_date'] = noxDate::toSql();
            $new['views'] = isset($new['views']) ? Views::array2int($new['views']) : 0;
            $size = @getimagesize(noxRealPath($new['filename']));
            if ($size) {
                $new['resolution_width'] = $size[0];
                $new['resolution_height'] = $size[1];
                $new['ext'] = strtolower(substr(strrchr($new['filename'], '.'), 1));
            }
            $new['full_name'] = Prints::generateFullName($new, $categoryData);
            $new['sort_name'] = Prints::generateSortName($new, $categoryData);
            $new['url'] = Prints::generateSEOUrl($new['full_name']);

            $this->model->updateById($id, $new);

            if(!empty($_POST['relatedVectorId']) && (new printsVectorModel)->where('id', $_POST['relatedVectorId'])->count()) {
                $printsRelationModel->blueprintVectorRelationModel->deleteByField('blueprint_id', $id);
                $printsRelationModel->blueprintVectorRelationModel->insert(['blueprint_id' => $id, 'vector_id' => $_POST['relatedVectorId']]);
            }

            //Пересчитываем количество чертежей марки
            if(isset($categoryData['make_id'])) {
                $itemsId = $categoryModel->select('id')->where('make_id', $categoryData['make_id'])->fetchAll(false, 'id');
                if(!$itemsId) {
                    $count = 0;
                }
                else {
                    $count = $this->model->reset()->where(['class_id' => $categoryId, 'item_id' => $itemsId])->count();
                }
                $this->makeModel->updateById($categoryData['make_id'], array('blueprints_count' => $count));
            }
            if(isset($_POST['locationBack'])) {
                noxSystem::location($_POST['locationBack']);
            }
            else {
                noxSystem::location('/administrator/prints/?section=blueprint&filter[category_id]=' . $categoryId
                    . '&filter[make_id]=' . $categoryData['make_id']);
            }
        }

        $this->addVar('category', $this->categories[$ar['class_id']]);
        $this->addVar('ar', $ar);
        $this->caption = 'Редактирование чертежа';

        $form->onlyFields = true;
        $categoryData = $categoryModel->getById($ar['item_id']);
        $this->addVar('categoryData', $categoryData);
        if(isset($form->model->fields['make_id'])) {
            $form->model->fields['make_id']['sql_where'] = 'class_id = ' . $categoryId;
            $form->addFieldsParams(['make_id' => ['class' => 'js-change-data-folder']]);
        }
        $form->setValues($categoryData);
        $this->addVar('f', $form);

        if(@$_SERVER['HTTP_REFERER']) {
            $this->addVar('locationBack', $_SERVER['HTTP_REFERER']);
        }
        $this->addVar('makeUrls', $this->makeModel->reset()->where('class_id', $categoryId)->fetchAll('id', 'url'));
    }

    /*
	 * Удаление чертежа
	 */
    public function actionDelete()
    {
        $id = getParam($_GET['id']);
        if (!$id) {
            return 400;
        }

        $old = $this->model->getById($id);
        $this->model->deleteById($id);

        //TODO: Пишем в лог
        $cat = $this->categories[$old['class_id']];
        $catModel = new noxModel(false, 'prints_class_' . $cat['db_table']);
        $cat['id'] && $catModel->deleteById($cat['id']);

        if(isset($catModel->fields['make_id'])) {
            //Пересчитываем количество чертежей марки
            $count = $catModel->countByField('make_id', $old['make_id']);
            $this->makeModel->updateById($old['make_id'], array('blueprints_count' => $count));
        }

        if ($this->ajax()) {
            return 200;
        } else {
            noxSystem::location('?section=blueprint');
        }
    }

    public function actionRelate2Vector() {
        if(isset($_POST['rel']) && (int)$_POST['rel']['blueprint_id'] > 0 && $_POST['rel']['vector_id']) {
            $relationModel = new printsRelationModel();
            $relationModel->blueprintVectorRelationModel->deleteByField('blueprint_id', $_POST['rel']['blueprint_id']);
            $relationModel->blueprintVectorRelationModel->replace($_POST['rel']);
        }

        $this->title = $this->caption = 'Связь чертежа с вектором';
        $blueprintId = $_GET['blueprint_id'];
        $blueprint = $this->model->getById($blueprintId);
        $this->addVar('blueprint', $blueprint);
    }

    public function actionMultiEdit()
    {
        if (isset($_GET['submit'])) {
            $new = current($_GET['new']);
            $id  = key($_GET['new']);

            $current = $this->model->getById($id);

            $categoryModel = new noxModel(false, 'prints_class_' . $this->categories[$current['class_id']]['db_table']);
            $form = new noxModelForm($categoryModel);

            $categoryData = $_GET[$form->getFormInputName()];

            unset($categoryData['id']);
            $categoryModel->updateById($current['item_id'], $categoryData);

            //Обновление информации о файле
            $new['update_date'] = noxDate::toSql();
            $new['views'] = isset($new['views']) ? Views::array2int($new['views']) : 0;
            $size = @getimagesize(noxRealPath($new['filename']));
            if ($size)
            {
                $new['resolution_width'] = $size[0];
                $new['resolution_height'] = $size[1];
                $new['ext'] = strtolower(substr(strrchr($new['filename'], '.'), 1));
            }

            $new['url'] = Prints::generateSEOUrl($new, $categoryData);

            $this->model->updateById($id, $new);

            //TODO: ведем лог

            //Пересчитываем количество чертежей марки
            if(isset($categoryData['make_id'])) {
                $count = $categoryModel->countByField('make_id', $categoryData['make_id']);
                $this->makeModel->updateById($categoryData['make_id'], array('blueprints_count' => $count));
            }
        }

        $this->title = $this->caption = 'Чертежи';

        $onPage = 20;
        $count = $this->model->reset()->count();
        $pagesCount = intval($count/$onPage)+1;
        $page = min(max(@getParam($_GET['page'], 1), 1), $pagesCount);

        $this->addVar('pagesCount', $pagesCount);
        $this->addVar('page', $page);

        $this->addVar('res', $this->model->limit(($page-1)*$onPage, $onPage)->order('id DESC')->fetchAll('id'));
    }
}
