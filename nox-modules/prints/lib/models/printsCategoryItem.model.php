<?php

class printsCategoryItemModel extends noxModel
{
    public $category;

    public function __construct($categoryId) {
        $this->category = (new printsCategoryModel())->getById($categoryId);
        if(!$this->category) return false;

        $this->table = 'prints_class_' . $this->category['db_table'];
        parent::__construct();
    }

    public function hasField($fieldName) {
        return isset($this->fields[$fieldName]);
    }
}
