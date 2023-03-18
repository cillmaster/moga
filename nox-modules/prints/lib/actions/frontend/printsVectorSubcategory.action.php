<?php
/**
 * Страница вывода подкатегории чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsVectorSubcategoryAction extends noxThemeAction
{
    public $category;
    public $subCategory;

    public $blueprintModel;

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
        $subCategoryUrl = $this->getParam('subcategory', '');
        $this->category = $categoryModel->getByUrl($categoryUrl);

        if(!$this->category) {
            return 404;
        }

        $isCars = $this->category['db_table'] == 'car';
        $categoryClassTable = 'prints_class_' . $this->category['db_table'];
        $dataCategoryModel = new noxModel(false, $categoryClassTable);

        $subCategoryName = false;

        $vectors = [];
        $requestVectorModel = new printsRequestVectorModel();
        $config = noxConfig::getConfig();
        $url = $config['protocol'] . $config['host'] . '/car-vector-drawings/' . $this->subCategory['url'];

        if(isset($dataCategoryModel->fields['make_id'])) {
            $subCategoryName = 'make_id';
            $makeModel = new printsMakeModel();
            $this->subCategory = $makeModel->getByUrl($subCategoryUrl, $this->category['id']);

            if(!$this->subCategory) {
                return 404;
            }

            $this->addVar('sectionMainName', $this->subCategory['name']);
            $subCategories = $makeModel->getActiveByCategory($this->category['id'], Prints::VECTOR);
            $this->addVar('subCategories', $subCategories);

            $res = $dataCategoryModel->where(['make_id' => $this->subCategory['id']])->fetchAll('id');
            if($res) {
                $vectors = $this->vectorModel->select('id, url, sort_name, full_name, views, prepay')->where(['item_id' => array_keys($res), 'class_id' => $this->category['id']])->fetchAll();
            }

            if($isCars) { // cars
                $this->templateFileName = $this->moduleFolder . '/templates/frontend/vectorCarsSubcategory.html';
                $page = $_GET['page'];
                $brv = new noxModel(null, 'brv_summary_car');
                $brv->where([
                    'make_id' => $this->subCategory['id'],
                    'type' => [0, 1, 2]
                ]);
                $this->addVar('pager', (new kafPager('pager2.html'))->create2($count = $brv->count(), $onPage = 100, 9, $page));
                $this->addVar('count', $count);
                if($page > 1){
                    $this->addVar('prevPage', $url . '?page=' . ($page - 1));
                }
                if($page * $onPage < $count){
                    $this->addVar('nextPage', $url . '?page=' . ($page + 1));
                }
                $bp = $brv->order('`sort_name`, `type`')->limit(($page - 1) * $onPage, $onPage)->fetchAll();
            } else {
                $requestedVectors = $requestVectorModel->select('id, url, sort_name, full_name, "15" as views')
                    ->where(['make_id' => $this->subCategory['id'], 'vector_id' => NULL])->fetchAll();
            }

            $this->addVar('subCategory', $this->subCategory);
            $make = $this->subCategory['name'];
            //====================TAGS=============================
            $useTagsModel = new tagCategoryUseModel();
            $keywords = '';
            foreach(explode(',', tagVectorModel::$vectorTags) as $tag) {
                $keywords .= $this->subCategory['name'] . ' ' . trim($tag) . ', ';
            }
            $useTags = $useTagsModel->getById($this->category['id']);
            foreach($useTags as $tag) {
                $keywords .= $this->subCategory['name'] . ' ' . $tag . ', ';
            }
            $keywords .= implode(', ', (new tagMakeModel())->getById($this->category['id']));
            $keywords .= tagCategoryUseModel::$useTags;
            $this->addMetaKeywords($keywords);
            //====================/TAGS=============================
            $this->vectorModel->reset();
            $src = array_column($vectors, 'id');
            if($isCars){
                $state = [
                    'titleVector' => false,
                    'relVectors' => [],
                    'sets' => []
                ];
                //noxSystem::$console->log(json_encode($src));
                if($src){
                    $paymentModel = new paymentModel();
                    $paidVectors = $paymentModel->select('DISTINCT `purchase_id`')
                        ->where(['status' => 'approved', 'purchase_id' => $src])
                        ->fetchAll(null, 'purchase_id');
                    if($paidVectors){
                        $src = array_values(array_diff($src, $paidVectors));
                        $state = $this->execVectors($state, $paidVectors);
                    }
                }
                //noxSystem::$console->log(json_encode($src));
                if($src && (count($state['relVectors']) < 4)){
                    $readyVectors = $this->vectorModel->reset()
                        ->where(['prepay' => '0', 'id' => $src])->fetchAll(null, 'id');
                    if($readyVectors){
                        $src = array_values(array_diff($src, $readyVectors));
                        $state = $this->execVectors($state, $readyVectors);
                    }
                }
                //noxSystem::$console->log(json_encode($src));
                if($src && (count($state['relVectors']) < 4)){
                    $prepayVectors = $this->vectorModel->reset()
                        ->where(['prepay' => '1', 'id' => $src])->fetchAll(null, 'id');
                    if($prepayVectors){
                        $state = $this->execVectors($state, $prepayVectors);
                    }
                }
                if($state['titleVector']){
                    if(isset($state['titleVector']['preview'])){ // check on prepay
                        $img_url = noxSystem::$media -> src($state['titleVector']['preview']);
                        $this->addVar('img_url', $img_url);
                        $this->addVar('image_src', $img_url);
                        $state['titleVector']['preview'] = $img_url;
                    } else {
                        $state['titleVector']['preview'] = '';
                    }
                    $this->addVar('titleVector', $state['titleVector']);
                }
                foreach ($state['relVectors'] as &$row) {
                    $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
                }
                $this->addVar('relVectors', $state['relVectors']);
            } else {
                $relVectorCount = 1 + max(0, floor((count($vectors) + count($requestedVectors) - 12) / 8));
                $relVectors = $this->vectorModel->getRelated($relVectorCount, ['id' => array_column($vectors, 'id')], ['class_id' => $this->category['id']]);
                $this->addVar('relVectors', $relVectors);
            }
        }
        else {
            $alphabet = getLatinAlphabet($this->category['id'], $this->vectorModel);
            $abc = strtoupper($subCategoryUrl);
            if(!in_array($abc, $alphabet)) {
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: /' . $this->category['url'] . '-vector-drawings');
                exit();
            }
            $this->addVar('sectionMainName', $this->category['name']);
            $this->addVar('subCategories', $alphabet);
            $make = $this->category['name_singular'];
            //====================TAGS=============================
            $useTagsModel = new tagCategoryUseModel();
            $keywords = '';
            foreach(explode(',', tagVectorModel::$vectorTags) as $tag) {
                $keywords .= $this->category['name_singular'] . ' ' . trim($tag) . ', ';
            }
            $useTags = $useTagsModel->getById($this->category['id']);
            foreach($useTags as $tag) {
                $keywords .= $this->category['name_singular'] . ' ' . $tag . ', ';
            }
            $keywords .= implode(', ', (new tagCategoryModel)->getById($this->category['id']));
            $keywords .= tagCategoryUseModel::$useTags;
            $this->addMetaKeywords($keywords);
            //====================/TAGS=============================

            $vectors = $this->vectorModel->select('id, url, sort_name, full_name, views, prepay')->where('`class_id` = "' . $this->category['id'] . '" AND name LIKE "' . $subCategoryUrl . '%"')->fetchAll();
            $requestedVectors = (new printsRequestVectorModel())->select('id, url, sort_name, full_name, "15" as views')->where('`category_id` = "' . $this->category['id'] . '" AND name LIKE "' . $subCategoryUrl . '%" AND `vector_id` IS NULL')->fetchAll();
            $relVectorCount = 1 + max(0, floor((count($vectors) + count($requestedVectors) - 12) / 8));
            $this->addVar('relVectors', (new printsVectorModel)->getRelated($relVectorCount, ['id' => array_column($vectors, 'id')], ['class_id' => $this->category['id']]));
        }
        if(isset($page) && $page > 1 && isset($count) && isset($onPage)){
            $this->title = 'Page ' . $page . ' of ' . ceil($count / $onPage) . ' for ' . $make . ' drawings';
            $metaDescription = '';
        } else {
            $class = ($isCars || isset($abc)) ? '' : (' ' . strtolower($this->category['name']));
            if(!$isCars && ($make == 'Other')){
                $class = ' ' . strtolower($this->category['db_table']);
            }
            $postFix = isset($abc) ? (' - ' . $abc) : '';
            $this->title = $make . $class . ' drawings collection download all models' . $postFix;
            $metaDescription = 'Download ' . $make . ' drawings, high resolution original drawings and scalable. '
                . 'Editable templates for ' . $make . ' wrap, vehicle branding and corporate design wrapping. '
                . 'Use it for T-shirt, birthday cake, poster or whatever.';
        }
        $this->addMetaDescription($metaDescription);
        $this->caption = $make . (isset($alphabet) ? (' '. $abc) : '') . ' drawings';

        if($_GET['search'] != '') {
            $raw = $_GET['search'];
            $params = [
                'category_id' => $this->category['id']
            ];


            if($subCategoryName) {
                $params[$subCategoryName] = $this->subCategory['id'];
            }
            if(@$raw['name']) {
                $params['name'] = $raw['name'];
            }
            if(@$raw['year']) {
                $params['year'] = $raw['year'];
            }

            $search = (new printsSearchModel())->search(false, $params);
            $this->addVar('search', $search);
            $this->addVar('canonical', $url);
        }
        else {
            if(isset($requestedVectors)) {
                $blueprints = [];
                foreach($vectors as $ar) {
                    $ar['type'] = $ar['prepay'] ? Prints::PREPAY : Prints::VECTOR;
                    $ar['bold'] = true;
                    $ar['tag'] = $ar['prepay'] ? Prints::HTML_ICON_PREPAY : Prints::HTML_ICON_VECTOR;
                    $ar['seo'] = 'vector drawings and templates';
                    $blueprints[] = $ar;
                }
                foreach($requestedVectors as $ar) {
                    $ar['type'] = Prints::REQUEST_VECTOR;
                    $ar['bold'] = false;
                    $ar['tag'] = Prints::HTML_ICON_VECTOR_REQUEST;
                    $ar['seo'] = 'drawings request';
                    $blueprints[] = $ar;
                }

                function sortBp($a1, $a2) {
                    return strcasecmp($a1['sort_name'], $a2['sort_name']);
                }

                usort($blueprints, 'sortBp');
                $this->addVar('blueprints', $blueprints);
            } else {
                foreach ($bp as &$ar){
                    switch ($ar['type']){
                        case 0:
                            $ar['type'] = Prints::VECTOR;
                            $ar['bold'] = true;
                            $ar['tag'] = Prints::HTML_ICON_VECTOR;
                            $ar['seo'] = 'vector drawings and templates';
                            break;
                        case 1:
                            $ar['type'] = Prints::PREPAY;
                            $ar['bold'] = true;
                            $ar['tag'] = Prints::HTML_ICON_PREPAY;
                            $ar['seo'] = 'vector drawings and templates';
                            break;
                        case 2:
                            $ar['type'] = Prints::REQUEST_VECTOR;
                            $ar['bold'] = true;
                            $ar['tag'] = Prints::HTML_ICON_VECTOR_REQUEST;
                            $ar['seo'] = 'drawings request';
                            break;
                    }
                }
                $this->addVar('blueprints', $bp);
            }
        }

        setcookie('my_search_q', '', time() - 1000, '/');
        setcookie('my_search_model',
            $this->category['id'] . '::'
            . (isset($this->subCategory['id']) ? $this->subCategory['id'] : '') . '::'
            . (isset($params['name']) ? $params['name'] : '') . '::::'
            . (isset($params['year']) ? $params['year'] : '')
            , 0, '/'
        );

        $this->addVar('subCategoryName', $subCategoryName);
        $breadcrumbs = [
            [
                'name' => 'drawings',
                'title' => 'drawings',
                'url'   => '/vector-drawings'
            ],
            [
                'name' => strtolower($this->category['name_singular']) . ' drawings',
                'title' => strtolower($this->category['name_singular']) . ' drawings',
                'url'   => '/' . $this->category['url'] . '-vector-drawings/'
            ],
        ];

        $this->addVar('breadcrumbs', $breadcrumbs);
        $this->addVar('category', $this->category);
        $this->addVar('subCategory', $this->subCategory);
    }

    public function execVectors($state, $vectors){
        shuffle($vectors);
        if(!$state['titleVector']){
            $state['titleVector'] = $this->tryGetSet(array_splice($vectors, 0, 1)[0]);
            if($state['titleVector']['set']){
                $state['sets'][] = $state['titleVector']['id'];
            }
        }
        while((count($state['relVectors']) < 4) && count($vectors)){
            if($res = $this->tryGetSet(array_splice($vectors, 0, 1)[0], $state['sets'])){
                if($res['set']){
                    $state['sets'][] = $res['id'];
                }
                $state['relVectors'][] = $res;
            }
        }
        return $state;
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
