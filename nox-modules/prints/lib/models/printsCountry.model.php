<?php

class printsCountryModel extends noxModel
{
    var $table = 'prints_country';

    public function getAll()
    {
        return $this->order('name')->fetchAll('id');
    }

    public function getList()
    {
        return $this->order('name')->fetchAll('id', 'name');
    }
}