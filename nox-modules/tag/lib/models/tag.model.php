<?php

class tagModel extends noxModel
{
    public $table = 'tag';
    public $columnTagIdName = 'tag_id';

    public function getTagsById($id, $asList) {
        $this->where('id', $id);
        if($asList) {
            return $this->fetchAll('id', 'tag');
        }
        else {
            return $this->fetchAll();
        }
    }

    public function insertTags($tags) {
        foreach($tags as $i=>$t) {
            $tags[$i] = ltrim($t);
        }
        $curTags = $this->select('id, tag')->where('tag', $tags)->fetchAll('tag', 'id');
        $insert = [];
        foreach($tags as $t) {
            if(!isset($curTags[$t])) {
                $insert[] = ['tag' => $t];
            }
        }
        $insert && $this->insert($insert);
    }

    public function getIdByTags($tags) {
        $this->insertTags($tags);
        return $this->where('tag', $tags)->fetchAll('tag', 'id');
    }
}