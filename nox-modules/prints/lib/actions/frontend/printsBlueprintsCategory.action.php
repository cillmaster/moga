<?php
/**
 * Страница вывода категории чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsBlueprintsCategoryAction extends noxThemeAction
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

        $isCars = $category['id'] == 1;
        if($isCars) { // cars
            $this->templateFileName = $this->moduleFolder . '/templates/frontend/blueprintsCarsCategory.html';
            $config = noxConfig::getConfig();
            $url = $config['protocol'] . $config['host'] . '/car-blueprints';
            $this->addVar('canonical', $url);
            $this->addVar('makeCars', (new printsMakeModel())->where([
                'class_id' => 1
            ])->order('name')->fetchAll());
            $this->title = 'Free car blueprints collection – find and download blueprint of any car on getoutlines.com';
            $this->caption = $category['name_singular'] . ' blueprints free';
            $this->addMetaDescription('Download free car blueprints and full-size bitmaps. Outlines helps designers '
                . 'and 3d artists to find the best car blueprint for car wrap and 3d modeling. Use images for design '
                . 'of car, wrapping, vinyl graphics and vehicle branding.');
        } else {
            $this->title = $this->caption = $category['name_singular'] . ' blueprints free';
            $this->addMetaDescription('Download free ' . strtolower($category['name_singular']) . ' blueprints and full-size bitmaps.' . tagBlueprintModel::$rasterMetaDescriptionAdditional);
        }

        //====================TAGS=============================
        $useTagsModel = new tagCategoryUseModel();
        $keywords = '';
        foreach(explode(',', tagBlueprintModel::$rasterTags) as $tag) {
            $keywords .= $category['name_singular'] . ' ' . trim($tag) . ', ';
        }
        $useTags = $useTagsModel->getById($category['id']);
        foreach($useTags as $tag) {
            $keywords .= $category['name_singular'] . ' ' . $tag . ', ';
        }
        $keywords .= implode(', ', (new tagCategoryModel)->getById($category['id']));
        $keywords .= tagCategoryUseModel::$useTags . ', ' . tagBlueprintModel::$rasterAdsTags;
        $this->addMetaKeywords($keywords);
        //====================/TAGS=============================

        $categoryClassTable = 'prints_class_' .$category['db_table'];
        $blueprintCategoryModel = new noxModel(false, $categoryClassTable);

        /*
         * Ищем подкатегорию, если она есть
         */
        $subCategoryName = false;
        if(isset($blueprintCategoryModel->fields['make_id'])) {
            $this->addVar('captionList', 'Make');
            $makeModel = new printsMakeModel();
            $subCategories = $makeModel->getActiveByCategory($category['id']);
            $subCategoryName = 'make_id';

        } else {
            $subCategories = getLatinAlphabet($category['id'], new printsBlueprintModel());
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
                'name' => 'blueprints',
                'title' => 'blueprints',
                'url'   => '/blueprints'
            ]
        ];

        $this->addVar('breadcrumbs', $breadcrumbs);
        $this->addVar('categories', $categoryModel->getActiveAll());
        $this->addVar('category', $category);
        if($isCars){
            $src = [802, 255, 94, 894, 2662, 66, 597, 31, 2240, 982, 1156, 1116, 880, 10779, 28013, 1467, 28116, 829, 596, 1320, 1536, 29, 1335, 2556, 283, 1330, 27847];
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
            $relVectorCount = 1 + max(0, floor((count($subCategories) - 6) / 7.4));
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
