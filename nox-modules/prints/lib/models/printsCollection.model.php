<?php

class printsCollectionModel extends noxModel
{
    public $table = 'prints_collection';
    public $collectionVectorModel;

    public function __construct() {
        parent::__construct();

        $this->collectionVectorModel = new printsCollectionVectorModel();
    }

    public function getAllNames() {
        return $this->select('name')->order('name')->fetchAll();
    }

    public function getNamesByFilter($q, $limit = 10) {
        return $this->select('name')
            ->where('name LIKE "%' . $q . '%"')
            ->order('name')
            ->limit($limit)
            ->fetchAll(null, 'name');
    }

    public function getCollectionsForVector($vectorId) {
        return $this->where("id IN(SELECT cID FROM {$this->collectionVectorModel->table} WHERE vID = {$vectorId})")
            ->fetchAll(null, 'id');
    }

    public function addVector($cID, $vID){
        $this->collectionVectorModel->insert([
            'cID' => $cID,
            'vID' => $vID
        ]);
    }

    public function getMap(){
        $res = [];
        $data = $this->collectionVectorModel->reset()->fetchAll();
        foreach ($data as $item){
            if(!isset($res[$item['cID']])){
                $res[$item['cID']] = [];
            }
            $res[$item['cID']][] = $item['vID'];
        }
        return $res;
    }

    public function getUniqueVectors(){
        return $this->collectionVectorModel->reset()->select('DISTINCT `vID`')->fetchAll(null, 'vID');
    }

    public function remove($cID, $vID){
        $this->collectionVectorModel->deleteByField([
            'cID' => $cID,
            'vID' => $vID
        ]);
    }
}

class printsCollectionVectorModel extends noxModel {
    public $table = 'prints_collection_vector';

    public function getVectorsIdByCollection($cID) {
        return $this->select('vID')->where('cID', $cID)->fetchAll(false, 'vID');
    }
}
