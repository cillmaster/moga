<?php
/**
 * Модель чертежа
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    car
 */

class printsClassesAttributesModel extends noxModel
{
    /**
     * Таблица модели
     * @var string
     */
    var $table = 'prints_class';

    private $classes = null;
    private $attributes = array();

    private $tprefix = 'prints_class_';

    public function getAll() {
        if(!$this->classes) {
            $this->loadClasses();
        }

        if(!$this->attributes) {
            foreach($this->classes as $class=>$ar) {
                $this->exec('DESCRIBE ' . $this->tprefix . $class);
                $this->attributes[$class] = $this->fetchAll();
            }
        }

        return $this->attributes;
    }

    public function loadClasses() {
        $this->classes = $this->fetchAll('name');
    }
}
