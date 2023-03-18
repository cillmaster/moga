<?php

class printsMakeModel extends noxModel
{
    public $table = 'prints_make';

    public function getByUrl($url, $categoryId) {
        return $this->where(['url' => $url, 'class_id' => $categoryId])->order('name')->fetch();
    }

    public function getAllByCategory($categoryId) {
        return $this->reset()->where('class_id', $categoryId)->order('name')->fetchAll('id');
    }

    public function getListByCategory($categoryId) {
        return $this->reset()->where('class_id', $categoryId)->order('name')->fetchAll('id', 'name');
    }

    public function getActiveByCategory($categoryId, $printsType = Prints::BLUEPRINT) {
        $categoryId = (int)$categoryId;
        if($printsType === Prints::BLUEPRINT) {
            return $this->reset()->where("class_id = $categoryId AND ((vectors_count > 0) OR (blueprints_count > 0) OR (id IN (SELECT make_id FROM `prints_request_vector`)))")->order('name')->fetchAll($this->id_field);
        }
        else {
            return $this->reset()->where("class_id = $categoryId AND ((vectors_count > 0 OR (id IN (SELECT make_id FROM `prints_request_vector`))))")->order('name')->fetchAll($this->id_field);
        }
    }

    public function getActiveAll($printsType = Prints::BLUEPRINT, $groupByCategory = false) {
        if($printsType === Prints::BLUEPRINT) {
            $this->reset()->where("(vectors_count > 0) OR (blueprints_count > 0)  OR (id IN (SELECT make_id FROM `prints_request_vector`))")->order('name');
        }
        else {
            $this->reset()->where("(vectors_count > 0)  OR (id IN (SELECT make_id FROM `prints_request_vector`))")->order('name');
        }

        if($groupByCategory) {
            $raw = $this->select('id, name, class_id as category_id')->fetchAll();
            $makes = [];
            foreach ($raw as $m) {
                $makes[$m['category_id']][$m['id']] = $m['name'];
            }
            return $makes;
        }
        else {
           return $this->fetchAll($this->id_field);
        }
    }
}