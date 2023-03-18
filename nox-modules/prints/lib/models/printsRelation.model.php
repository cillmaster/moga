<?php

class printsRelationModel
{
    public $blueprintVectorRelationModel;

    public function __construct() {
        $this->blueprintVectorRelationModel = new noxModel(false, 'prints_relation_vector_blueprint');
    }

    public function getVectorIdForBlueprint($blueprintId) {
        return $this->blueprintVectorRelationModel->where('blueprint_id', $blueprintId)->fetch('vector_id');
    }
}