<?php

class tagParentModel extends noxModel {

    public $tagModel;
    public $categoryName = 'category_id';
    public $table = 'tag_category';

    public function __construct() {
        $this->tagModel = new tagModel();
        parent::__construct();
    }

    public function getById($id, $asList = true) {
        $res = $this->where($this->categoryName, $id)->fetchAll(false, $this->tagModel->columnTagIdName);
        if($res) {
            return $this->tagModel->getTagsById($res, $asList);
        }
        else {
            return [];
        }
    }

    public function saveTags($tags, $id) {
        $tagIds = $this->tagModel->getIdByTags($tags);
        $insert = [];
        foreach($tagIds as $tag=>$tagId) {
            $insert[] = [
                $this->categoryName => $id,
                $this->tagModel->columnTagIdName => $tagId
            ];
        }
        $this->deleteByField($this->categoryName, $id);
        $this->insert($insert);
    }
}