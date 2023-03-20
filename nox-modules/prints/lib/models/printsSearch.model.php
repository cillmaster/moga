<?php
include_once noxRealPath('nox-modules/3rdparty/SphinxQB/vendor/autoload.php');
use Foolz\SphinxQL\Connection;
use Foolz\SphinxQL\SphinxQL;

class printsSearchModel extends noxModel {

    public $blueprintModel;
    public $vectorModel;
    public $makeModel;

    private $categoryModel = NULL;
    private $categories;
    private $tablesPrefix = 'prints_class_';

    public $makeData;
    public $items;

    public $table = 'nox_user';

    private function goNowIfCan($query) {
        $res = (new printsSetModel())->where('`name_title` = "' . $query . '" OR `name_full` = "' . $query . '"')->fetchAll();
        if(count($res) == 1) {
            noxSystem::location(Prints::createUrlForItem($res[0], Prints::SET_VECTOR));
        }
/*
        $res = (new printsMakeModel())->where('name', $query)->fetchAll();
        if(count($res)) {
            $category = (new printsCategoryModel())->getById($res[0]['class_id']);
            noxSystem::location('/' . $category['url'] . '-vector-drawings/' . $res[0]['url']);
        }
*/
    }

    public function searchSphinx($query, $params){
        noxSystem::$console->log(SPHINX_MODE);
        $response = [
            'params' => $params,
            'query' => $query
        ];

        $query = $this->escape(str_replace('%', '', urldecode($query)));

        if(!in_array(strtoupper($query), array('GT', 'GP', 'F1', 'F2', 'F1000', 'COUPE',
            'OPEL', 'JEEP', 'GENESIS'))){
            $this->goNowIfCan($query);
        }
        if($query) {
            $params['name'] = $query;
        }

        if(isset($params['name']) && strlen($params['name']) < 2) {
            $response['error'] = 'Enter 2 or more characters to search. Otherwise use <a href="/blueprints">blueprint catalogue</a>';
        }
        elseif($params) {
            $rest = 100;
            $response['relVectors'] = [];
            $response['result'] = [];
            if(isset($params['category_id'])){
                $where[] = 'category_id = ' . $params['category_id'];
            }
            if(isset($params['make_id'])){
                $where[] = 'make_id = ' . $params['make_id'];
            }
            if(isset($params['name'])){
                $where[] = '(MATCH(\'(@search_name "' .  $this->escape(str_replace(['\\', '/'], ' ', $params['name'])) . '"~100)\'))';
            }
            if(isset($params['year'])){
                $where[] = 'year = ' . $params['year'];
            }
            if(isset($where)){
                $where = join(' AND ', $where);
                $conn = new Connection();
                $conn->silenceConnectionWarning();
                $query = SphinxQL::create($conn)->query('SELECT * FROM vector_' . SPHINX_MODE . ' WHERE ' . $where . ' LIMIT ' . $rest . ' OPTION max_matches = ' . $rest)
                    ->enqueue(SphinxQL::create($conn)->query('SHOW META'))
                    ->executeBatch();
                $rest = max($rest - sizeof($query[0]), 0);
                $response['relVectors'] = $query[0];
                foreach($response['relVectors'] as $ar) {
                    $response['result'][] = [
                        'url' => Prints::createUrlForItem($ar, Prints::VECTOR),
                        'title' => $ar['full_name'],
                        'type' => $ar['prepay'] ? Prints::PREPAY : Prints::VECTOR,
                        'tag' => $ar['prepay'] ? Prints::HTML_ICON_PREPAY : Prints::HTML_ICON_VECTOR,
                        'bold' => true,
                        'data' => [
                            'views' => $ar['views'],
                            'sort_name' => $ar['sort_name'],
                            'preview' => $ar['preview']
                        ]
                    ];
                }
                if($rest){
                    $query = SphinxQL::create($conn)->query('SELECT * FROM request_' . SPHINX_MODE . ' WHERE ' . $where . ' LIMIT ' . $rest . ' OPTION max_matches = ' . $rest)
                        ->enqueue(SphinxQL::create($conn)->query('SHOW META'))
                        ->executeBatch();
                    $rest = max($rest - sizeof($query[0]), 0);
                    foreach($query[0] as $ar) {
                        $response['result'][] = [
                            'url' => Prints::createUrlForItem($ar, Prints::REQUEST_VECTOR),
                            'title' => $ar['full_name'],
                            'type' => Prints::REQUEST_VECTOR,
                            'bold' => true,
                            'tag' => Prints::HTML_ICON_VECTOR_REQUEST,
                            'data' => [
                                'views' => 15,
                                'sort_name' => $ar['sort_name']
                            ]
                        ];
                    }
                }
                if($rest){
                    $query = SphinxQL::create($conn)->query('SELECT * FROM blueprint_' . SPHINX_MODE . ' WHERE ' . $where . ' LIMIT ' . $rest . ' OPTION max_matches = ' . $rest)
                        ->enqueue(SphinxQL::create($conn)->query('SHOW META'))
                        ->executeBatch();
                    foreach($query[0] as $ar) {
                        $response['result'][] = [
                            'url' => Prints::createUrlForItem($ar, Prints::BLUEPRINT),
                            'title' => $ar['full_name'],
                            'type' => Prints::BLUEPRINT,
                            'bold' => false,
                            'tag' => '',
                            'data' => [
                                'views' => $ar['views'],
                                'sort_name' => $ar['sort_name']
                            ]
                        ];
                    }
                }
            }else{
                $response['error'] = 'Empty request!';
            }
        }
        if(isset($response['result']) && sizeof($response['result']) > 0) {
            function sortBpD($a1, $a2) {
                return strcasecmp($a1['data']['sort_name'], $a2['data']['sort_name']);
            }
            usort($response['result'], 'sortBpD');
        }
        return $response;
    }

    public function searchSphinx2($query, $params = false, $start = 0, $size = 100){
        noxSystem::$console->log(SPHINX_MODE);
        $response = [
            'params' => $params,
            'query' => $query
        ];

        $query = $this->escape(str_replace('%', '', urldecode($query)));

        if($query) {
            $params['name'] = $query;
        }


        if(isset($params['name']) && strlen($params['name']) < 2) {
            $response['error'] = 'Enter 2 or more characters to search. Otherwise use <a href="/blueprints">blueprint catalogue</a>';
        }
        elseif($params) {
            $rest = 100;
            $response['sets'] = [];
            $response['rest'] = [];
            $response['result'] = [];

            if(isset($params['category_id'])){
                $where[] = 'category_id = ' . $params['category_id'];
            }

            if(isset($params['make_id'])){
                $where[] = 'make_id = ' . $params['make_id'];
            }

            if(isset($params['name'])){
                $where[] = '(MATCH(\'(@search_name "' .  $this->escape(str_replace(['\\', '/'], ' ', $params['name'])) . '"~100)\'))';
            }

            if(isset($params['year'])){
                $where[] = 'year = ' . $params['year'];
            }

            if(isset($where)){
                $where = join(' AND ', $where);
                $conn = new Connection();
                $conn->silenceConnectionWarning();

                $query = SphinxQL::create($conn)->query('SELECT `id` FROM vector_' . SPHINX_MODE . ' WHERE ' . $where . ' LIMIT 10000 OPTION max_matches = 10000')
                    ->enqueue(SphinxQL::create($conn)->query('SHOW META'))
                    ->executeBatch();

                $idArr = [];
                foreach ($query[0] as $item){
                    $idArr[] = $item['id'];
                }

                $map = (new printsSetModel())->getSetsForVectors($idArr);

                $response = array_merge($response, $map);
                $response['vectorTotal'] = count($idArr);

                $max = $start + $size;

//                $query = SphinxQL::create($conn)->query('SELECT * FROM brv_' . SPHINX_MODE . ' WHERE '
//                    . $where . ' ORDER BY `sort_name` ASC, `type` ASC LIMIT ' . $start . ', ' . $size
//                    . ' OPTION max_matches = ' . $max)
//                    ->enqueue(SphinxQL::create($conn)->query('SHOW META'))
//                    ->executeBatch();

                $response['total'] = $response['setsTotal'];//$query[1][1]['Value'];

                foreach ($query[0] as &$ar){

                    $ar['id'] = floor($ar['id'] / 10);
                    /*
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
                        case 3:
                            $ar['type'] = Prints::BLUEPRINT;
                            $ar['bold'] = false;
                            $ar['tag'] = '';
                            $ar['seo'] = 'blueprints free';
                            break;
                    }

                    $ar['url'] = Prints::createUrlForItem($ar, $ar['type']);
                    */
                }
                $response['result'] = $query[0];

//                $query = SphinxQL::create($conn)->query('SELECT * FROM blueprint_' . SPHINX_MODE . ' WHERE '
//                    . $where . ' OPTION max_matches = 10000')
//                    ->enqueue(SphinxQL::create($conn)->query('SHOW META'))
//                    ->executeBatch();
                $response['rasterTotal'] = 0;//$query[1][1]['Value'];
            }else{
                $response['error'] = 'Empty request!';
            }
        }

        return $response;
    }

    /**
     * @param $query
     * @param $params mixed bool|array
     * @return array
     * [
     *  query => string
     *  params => mixed
     *  result => array OR error => string
     * ]
     */

    public function search($query, $params = false) {
        if((noxConfig::getConfig())['searchEngine'] == 'sphinx'){
            return $this->searchSphinx($query, $params);
        }
        $response = [
            'params' => $params,
            'query' => $query
        ];

        $query = $this->escape(str_replace('%', '', $query));

        if(!in_array(strtoupper($query), array('GT', 'GP', 'F1', 'F2', 'F1000', 'COUPE',
            'OPEL', 'JEEP', 'GENESIS'))){
            $this->goNowIfCan($query);
        }

        $this->categoryModel = new printsCategoryModel();
        $this->categories = $this->categoryModel->getActiveAll();

        $this->blueprintModel = new printsBlueprintModel();
        $this->vectorModel = new printsVectorModel();
        $this->makeModel = new printsMakeModel();

        $this->makeData = $this->makeModel->select('id, name, url')->fetchAll('id');

        foreach($this->categories as &$c) {
            $c['db'] = new noxModel(false, $this->tablesPrefix . $c['db_table']);
        }

        if($query) {
            $params['name'] = $query;
        }

        if(isset($params['name']) && strlen($params['name']) < 2) {
            $response['error'] = 'Enter 2 or more characters to search. Otherwise use <a href="/blueprints">blueprint catalogue</a>';
        }
        elseif($params) {
            $where = '';
            $setCategory = isset($params['category_id']) && isset($this->categories[$params['category_id']]);
            if($setCategory || isset($params['name'])) {
                if(isset($params['name']) && $setCategory) {
                    $where .= 'full_name LIKE "%' . $this->escape($params['name']) . '%" AND class_id = ' . $params['category_id'];
                }
                elseif($setCategory) {
                    $where .= 'class_id = ' . $params['category_id'];
                }
                else {
                    $where .= 'full_name LIKE "%' . $this->escape($params['name']) . '%"';
                }
            }

            if($setCategory && (isset($params['make_id']) || isset($params['year']))) {
                if($where) {
                    $where .= ' AND ';
                }

                if(isset($params['year']) && isset($params['make_id'])) {
                    $where .= 'item_id IN(SELECT id FROM `' . $this->tablesPrefix . $this->categories[$params['category_id']]['db_table'] . '` WHERE make_id = ' . (int)$params['make_id'] . ' AND `year` = ' . (int)$params['year'] . ')';
                }
                elseif(isset($params['year'])) {
                    $where .= 'item_id IN(SELECT id FROM `' . $this->tablesPrefix . $this->categories[$params['category_id']]['db_table'] . '` WHERE `year` = ' . (int)$params['year'] . ')';
                }
                else {
                    $where .= 'item_id IN(SELECT id FROM `' . $this->tablesPrefix . $this->categories[$params['category_id']]['db_table'] . '` WHERE make_id = ' . (int)$params['make_id'] . ')';
                }
            }
            if($where) {
                $response['result'] = [];
                $response['where'] = $where;
                $resVector = $this->vectorModel->select('id, url, class_id as category_id, item_id, name, name_version, name_spec, sort_name, full_name, views, preview, price, prepay, prm')->where($where)->fetchAll();
                $resBlueprint = $this->blueprintModel->select('id, url, class_id as category_id, item_id, sort_name, full_name, views')->where($where)->fetchAll();

                $requestWhere = 'vector_id IS NULL';
                if(isset($params['year'])) {
                    if($requestWhere !== '') $requestWhere .= ' AND ';
                    $requestWhere .= ' year = "' . $params['year'] . '"';
                }
                if(isset($params['make_id'])) {
                    if($requestWhere !== '') $requestWhere .= ' AND ';
                    $requestWhere .= ' make_id = "' . $params['make_id'] . '"';
                }
                if(isset($params['category_id'])) {
                    if($requestWhere !== '') $requestWhere .= ' AND ';
                    $requestWhere .= ' category_id = "' . $params['category_id'] . '"';
                }
                if(isset($params['name'])) {
                    if($requestWhere !== '') $requestWhere .= ' AND ';
                    $requestWhere .= ' full_name LIKE "%' . $params['name'] . '%"';
                }

                $resRequestVector = (new printsRequestVectorModel)->where($requestWhere)->fetchAll();
                $itemsIds = [];
                foreach($resBlueprint as $ar) {
                    $itemsIds[$ar['category_id']][] = $ar['item_id'];
                }
                foreach($resVector as $ar) {
                    $itemsIds[$ar['category_id']][] = $ar['item_id'];
                }

                foreach($itemsIds as $categoryId=>$itemIds) {
                    $this->items[$categoryId] = $this->categories[$categoryId]['db']->reset()->where('id', $itemIds)->fetchAll('id');
                }

                foreach($resBlueprint as $ar) {
                    $response['result'][] = [
                        'url' => Prints::createUrlForItem($ar, Prints::BLUEPRINT),
                        'title' => $ar['full_name'],
                        'type' => Prints::BLUEPRINT,
                        'data' => [
                            'views' => $ar['views'],
                            'sort_name' => $ar['sort_name']
                        ]
                    ];
                }
                foreach($resVector as $ar) {
                    $response['result'][] = [
                        'url' => Prints::createUrlForItem($ar, Prints::VECTOR),
                        'title' => $ar['full_name'],
                        'type' => Prints::VECTOR,
                        'data' => [
                            'views' => $ar['views'],
                            'sort_name' => $ar['sort_name'],
                            'preview' => $ar['preview']
                        ]
                    ];
                }
                foreach($resRequestVector as $ar) {
                    $response['result'][] = [
                        'url' => Prints::createUrlForItem($ar, Prints::REQUEST_VECTOR),
                        'title' => $ar['full_name'],
                        'type' => Prints::REQUEST_VECTOR,
                        'data' => [
                            'views' => 15,
                            'sort_name' => $ar['sort_name']
                        ]
                    ];
                }
            }
            else {
                $response['error'] = 'Empty request!';
            }

        }
        if(isset($resVector)) {
            $response['relVectors'] = $resVector;
        }

        if(isset($response['result'])) {
            function sortBpD($a1, $a2) {
                return strcasecmp($a1['data']['sort_name'], $a2['data']['sort_name']);
            }
            usort($response['result'], 'sortBpD');
        }

        return $response;
    }
}
