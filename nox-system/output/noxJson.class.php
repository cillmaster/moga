<?php

/**
 * Class noxJson
 * @property string error
 * @property string action
 * @property string message
 */
class noxJson {

    private $fields = [];

    public $options = 0;

    public function __set($name, $value) {
        $this->fields[$name] = $value;
    }

    public function __get($name) {
        if(isset($this->fields[$name])) {
            return $this->fields[$name];
        }
        else {
            return NULL;
        }
    }

    public function __isset($name) {
        return isset($this->fields[$name]);
    }

    public function __toString() {
        return json_encode($this->fields, $this->options | JSON_UNESCAPED_SLASHES);
    }

    public function getData() {
        return $this->fields;
    }
}