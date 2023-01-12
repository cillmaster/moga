<?php
/**
 * Страница вывода категории чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsVectorCategoryAction extends noxThemeAction
{
    public $vectorModel;
    public $setModel;

    public function __construct(){
        $this->vectorModel = new printsVectorModel();
        $this->setModel = new printsSetModel();
        parent::__construct();
    }

    public function execute()
    {
        $categoryModel = new printsCategoryModel();
        $categoryUrl = $this->getParam('category', '');
        $category  = $categoryModel->getByUrl($categoryUrl);

        if(!$category) {
            return 404;
        }

        $isCars = $category['db_table'] == 'car';
        if($isCars) { // cars
            $this->templateFileName = $this->moduleFolder . '/templates/frontend/vectorCarsCategory.html';
            $this->addVar('makeCars', (new printsMakeModel())->where([
                'class_id' => 1
            ])->order('name')->fetchAll());
            $this->title = 'Car vector line drawings for sale. Get drawing of any car on getoutlines.com';
            $this->caption = $category['name_singular'] . ' drawings';
            $this->addMetaDescription('Purchase premium quality car drawings and editable vector car clip art. '
                . 'Buy ready files or order blueprint of any car. Use auto line drawing and diagram for design, '
                . 'car sign writing and livery. Start using vector plans of vehicles today and simplify your '
                . 'sketch process.');
        } else {
            $this->title = $this->caption = $category['name_singular'] . ' drawings';
            $this->addMetaDescription('Download ' . strtolower($category['name_singular']) . ' drawings, hi-res blueprints and scalable outlines.');
        }

        $categoryClassTable = 'prints_class_' . $category['db_table'];
        $blueprintCategoryModel = new noxModel(false, $categoryClassTable);

        //====================TAGS=============================
        $useTagsModel = new tagCategoryUseModel();
        $keywords = '';
        foreach(explode(',', tagVectorModel::$vectorTags) as $tag) {
            $keywords .= $category['name_singular'] . ' ' . trim($tag) . ', ';
        }
        $useTags = $useTagsModel->getById($category['id']);
        foreach($useTags as $tag) {
            $keywords .= $category['name_singular'] . ' ' . $tag . ', ';
        }
        $keywords .= implode(', ', (new tagCategoryModel)->getById($category['id']));
        $keywords .= tagCategoryUseModel::$useTags;
        $this->addMetaKeywords($keywords);
        //====================/TAGS=============================

        /*
         * Ищем подкатегорию, если она есть
         */
        $subCategoryName = false;
        if(isset($blueprintCategoryModel->fields['make_id'])) {
            $this->addVar('captionList', 'Make');
            $makeModel = new printsMakeModel();
            $subCategories = $makeModel->getActiveByCategory($category['id'], Prints::VECTOR);
            $subCategoryName = 'make_id';

        } else {
            $subCategories = getLatinAlphabet($category['id'], new printsVectorModel());
            $subCategories = array_map(function($letter) {
                return ['name' => $letter, 'url' => $letter];
            }, $subCategories);
        }
        $this->addVar('subCategories', $subCategories);

        $this->addVar('subCategoryName', $subCategoryName);

        if($_GET['search'] != '') {
            $raw = $_GET['search'];
            $params = [
                'category_id' => $category['id']
            ];

            if($subCategoryName) {
                if(@$raw[$subCategoryName]) {
                    $params[$subCategoryName] = $raw[$subCategoryName];
                }
            }

            if(@$raw['name']) {
                $params['name'] = $raw['name'];
            }
            if(@$raw['year']) {
                $params['year'] = $raw['year'];
            }

            $search = (new printsSearchModel())->search(false, $params);
            $this->addVar('search', $search);
        }

        setcookie('my_search_q', '', time() - 1000, '/');
        setcookie('my_search_model',
            $category['id'] . '::'
            . (isset($params[$subCategoryName]) ? $params[$subCategoryName] : '') . '::'
            . (isset($params['name']) ? $params['name'] : '') . '::::'
            . (isset($params['year']) ? $params['year'] : '')
            , 0, '/'
        );

        $breadcrumbs = [
            [
                'name' => 'drawings',
                'title' => 'drawings',
                'url'   => '/vector-drawings'
            ]
        ];

        $this->addVar('breadcrumbs', $breadcrumbs);
        $this->addVar('categories', $categoryModel->getActiveAll());
        $this->addVar('category', $category);
        if($isCars){
            $src = [547, 1398, 210, 1146, 1729, 643, 808, 2168, 1137, 12063, 875, 1064, 905, 1034, 813, 1019, 1295, 2806, 28265, 28078, 29, 519, 674, 1479, 14618, 476, 1434, 762, 11972, 1319, 27796];
            shuffle($src);
            $sets = [];
            $res = $this->tryGetSet(array_splice($src, 0, 1)[0]);
            if(isset($res['preview'])){
                $img_url = noxSystem::$media -> src($res['preview']);
                $this->addVar('img_url', $img_url);
                $this->addVar('image_src', $img_url);
                $res['preview'] = $img_url;
            } else {
                $res['preview'] = '';
            }
            if($res['set']){
                $sets[] = $res['id'];
            }
            $this->addVar('titleVector', $res);
            $relVectors = [];
            while((count($relVectors) < 4) && count($src)){
                if($res = $this->tryGetSet(array_splice($src, 0, 1)[0], $sets)){
                    if($res['set']){
                        $sets[] = $res['id'];
                    }
                    $relVectors[] = $res;
                }
            }
            foreach ($relVectors as &$row) {
                $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
            }
            $this->addVar('relVectors', $relVectors);
        } else {
            $relVectorCount = 1 + max(0, floor((count($subCategories) - 14) / 5.6));
            $this->addVar('relVectors', $this->vectorModel->getRelated($relVectorCount, ['class_id' => $category['id']]));
        }
    }

    public function tryGetSet($vectorId, $ignore = []){
        if($res = $this->setModel->getSetForVector($vectorId)){
            if(!in_array($res['id'], $ignore)){
                $res['c'] = count($vectorsInSet = $this->setModel->setVectorModel->getVectorsIdBySet($res['id']));
                $res['price'] = $this->vectorModel->where(['id' => $vectorsInSet])->order('price')->limit(1)->fetch('price');
                $vector = $this->vectorModel->where(['id' => $vectorId])->fetch();
                $res['preview'] = $vector['preview'];
                $res['vector'] = !$vector['prepay'];
                $res['set'] = true;
            } else {
                return false;
            }
        } else {
            $res = $this->vectorModel->getById($vectorId);
            $res['set'] = false;
        }
        return $res;
    }
}
