<?php

class printsSetModel extends noxModel
{
    public $table = 'prints_set';
    public $setVectorModel;

    public function __construct() {
        parent::__construct();

        $this->setVectorModel = new printsSetVectorModel();
    }

    public function clear($vectorID = 0){
        if($vectorID) {
            $this->setVectorModel->deleteByField('vector_id', $vectorID);
        }
        $noxDbQuery = new noxDbQuery();
        $noxDbQuery->exec('SELECT `id`, COUNT(`vector_id`) c FROM `prints_set` 
          LEFT JOIN `prints_set_vector` ON `set_id` = `id` 
          GROUP BY `id` HAVING c = 0');
        if($dead = $noxDbQuery->fetchAll(null, 'id')){
            $this->deleteByField('id', $dead);
        }
    }

    public function updateSets($item, $oldItem = false) {
        $field = 'name';

        if($oldItem) {
            $this->clear($oldItem['id']);
        }

        $categoryModel = new printsCategoryItemModel($item['class_id']);
        if($categoryModel->hasField('make_id')) {
            $categoryData = $categoryModel->getById($item['item_id']);
            $res = (new printsVectorModel())->where(
                "`{$field}` = '{$item[$field]}'  AND item_id IN(SELECT id FROM {$categoryModel->table} WHERE make_id = {$categoryData['make_id']})"
            )->fetchAll();
        }
        else {
            $res = (new printsVectorModel())->where($field, $item[$field])->fetchAll();
        }

        if(sizeof($res) > 1) {
            if($categoryModel->hasField('make_id')) {
                $setName = (new printsMakeModel())->where('id', $categoryData['make_id'])->fetch('name') . ' ';
            }
            else {
                $setName = '';
            }
            $setName .= $item[$field];
            $set = [
                'name_title' => $item[$field],
                'name_full' => $setName,
                'url' => URLTools::string2url($setName)
            ];
            $this->replace($set);

            $set = $this->getByField('url', $set['url']);

            foreach($res as $ar) {
                $this->setVectorModel->replace([
                    'set_id' => $set['id'],
                    'vector_id' => $ar['id']
                ]);
            }
        }
        return isset($set) ? $set['id'] : 0;
    }

    public function getAllNames() {
        return $this->select('name_full')->order('name_full')->fetchAll();
    }

    public function getNamesByFilter($q, $limit = 10) {
        return $this->select('name_full')
            ->where('name_full LIKE "%' . $q . '%"')
            ->order('name_full')
            ->limit($limit)
            ->fetchAll(null, 'name_full');
    }

    public function getAllByMake($makeId) {
        $makeName = (new printsMakeModel())->select('name')->where('id', $makeId)->fetch('name');
        return $this->where('`name_full` LIKE "' . $makeName . '%"')->order('name_title')->fetchAll('id');
    }

    public function getSetForVector($vectorId) {
        return $this->where("id IN(SELECT set_id FROM {$this->setVectorModel->table} WHERE vector_id = {$vectorId})")->fetch();
    }

    public function getSetsForVectors($vArr, $limit = 30, $ignore = []){
        $res = ['sets' => [], 'rest' => [], 'setsTotal' => 0];
        if(count($vArr)){
            $dbQuery = new noxDbQuery();
            $mapTable = $this->setVectorModel->table;
            $vList = join(',', $vArr);
            $where = 'id IN(SELECT DISTINCT set_id FROM ' . $mapTable . ' WHERE vector_id IN(' . $vList . '))';
            if($ignore){
                $where .= ' AND id NOT IN(' . $ignore . ')';
            }
            $res['setsTotal'] = $this->where($where)->count();
            if($res['setsTotal']){
                $sets = $this->limit($limit)->fetchAll(null, 'id');
                $sql = 'SELECT `prints_set`.`id`, `prints_set`.`url`, `prints_set`.`name_full`, '
                    . 'COUNT(`vector_id`) c, MIN(`price`) price, MIN(`preview`) preview '
                    . 'FROM `prints_set` '
                    . 'LEFT JOIN `prints_set_vector` ON `prints_set`.`id` = `prints_set_vector`.`set_id` '
                    . 'LEFT JOIN `prints_vector` ON `prints_vector`.`id` = `prints_set_vector`.`vector_id` '
                    . 'WHERE `prints_set`.`id` IN(' . join(', ', $sets) . ') '
                    . 'GROUP BY `prints_set`.`id` '
                    . 'ORDER BY `preview`';
                $dbQuery->exec($sql);
                $res['sets'] = $dbQuery->fetchAll();
            } else {
                $sets = [];
            }
            if($rest = $limit - count($sets)){
                $sql = 'SELECT `prints_vector`.* '
                    . 'FROM `prints_vector` '
                    . 'LEFT JOIN `prints_set_vector` ON `prints_vector`.`id` = `prints_set_vector`.`vector_id` '
                    . 'WHERE `id` IN(' . $vList . ') '
                    . 'AND `set_id` IS NULL '
                    . 'ORDER BY `prepay` '
                    . 'LIMIT ' . $rest;
                $dbQuery->exec($sql);
                $res['rest'] = $dbQuery->fetchAll();
            }
        }
        return $res;
    }

    public static function viewSetPreview($v, $urlType = false, $extra = '') {
        $url = Prints::createUrlForItem($v, Prints::SET_VECTOR, $urlType);
        $turlType = str_replace('-', ' ', $urlType);
        $price = [(int)$v['price'], ($v['price']-(int)$v['price'])*100];
        $v['price'] = $price[0] . (($price[1]) ? '<sup>.' . $price[1] . '</sup>' : '');
        $prepaySize = preg_match('/large/', $extra) ? 448 : 208;

        $tmplReady = '<div class="block vector-block-preview %11$s">
                <div class="cont">
                    <a href="%1$s" title="%9$s blueprint and drawing">
                        <img src="%3$s" alt="%9$s blueprint">
                    </a>
                    <div class="info bottom">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">Collection of %10$s blueprints</div>
                        <div class="orange">Starting at $%4$s</div>
                    </div>
                </div>
                <div title="%9$s blueprint and drawing" class="vector-mark"></div>
                <div class="popup">
                    <div class="info">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">Collection of %10$s blueprints</div>
                    </div>' .
                '<a class="btn hover" href="%1$s"  title="%9$s blueprint and drawing" style="font-size:14px">View set of %10$s blueprints</a>'
            . '</div>
            </div>';

        $tmplPrepay = '<div class="block vector-block-preview %11$s">
                <div class="cont">
                    <a href="%1$s" title="%9$s blueprint and drawing" class="prepay-link-cont">
                        <img src="/nox-themes/default/images/prepay-preview%12$s.png" alt="%9$s blueprint" class="prepay-image">
                        <img src="/nox-themes/default/images/prepay-magic%12$s.png" alt="%9$s blueprint" class="prepay-image-hover">
                    </a>
                    <div class="info bottom">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">Collection of %10$s blueprints</div>
                        <div class="orange">Starting at $%4$s</div>
                    </div>
                </div>
                <div class="popup">
                    <div class="info">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">Collection of %10$s blueprints</div>
                    </div>' .
            '<a class="btn hover" href="%1$s"  title="%9$s blueprint and drawing" style="font-size:14px">View set of %10$s blueprints</a>'
            . '</div>
            </div>';

        return sprintf($v['vector'] ? $tmplReady : $tmplPrepay,
                $url,
                '',
                $v['preview'],
                $v['price'],
                $v['id'],
                $turlType,
                'Download for',
                'Set of items',
                $v['name_full'],
                $v['c'],
                $extra,
                $prepaySize
            );
    }

}

class printsSetVectorModel extends noxModel {
    public $table = 'prints_set_vector';

    public function getVectorsIdBySet($setId) {
        return $this->select('vector_id')->where('set_id', $setId)->fetchAll(false, 'vector_id');
    }
}
