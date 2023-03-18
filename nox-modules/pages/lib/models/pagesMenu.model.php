<?php
/**
 * pagesMenuModel
 *
 * Модель меню.
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    pages
*/

class pagesMenuModel extends noxModel
{
    /**
     * Таблица модели
     *
     */
    var $table = 'pages_menu';

    /**
     * Возвращает меню заданного родителя в виде древовидного массива
     *
     * @param int
     * @return array
     */
    public function getByParentId($parent_id)
    {
        //Создаем статическую переменную, чтобы каждый раз заново не делать запрос
        static $link = false;
        static $res = false;
        if (!$link)
        {
            $res = array('childsCount' => 0);
            $link[0] = &$res;
            //Получаем запросом с сортировкой
            $this->reset()->order('sort');
            while ($ar = $this->fetch())
            {
                //Количество детей
                $c = $link[$ar['parent_id']]['childsCount'];

                $link[$ar['parent_id']]['childsCount']++;

                $ar['childsCount'] = 0;
                $ar['childs'] = array();
                $link[$ar['parent_id']]['childs'][$c] = $ar;

                $link[$ar['id']] = &$link[$ar['parent_id']]['childs'][$c];
            }
        }
        return @$link[$parent_id]['childs'];
    }

    /**
     * Возвращает меню заданного по Заголовку родителя в виде древовидного массива
     *
     * @param string $parent_title
     * @return array
     */
    public function getByParentTitle($parent_title)
    {
        //Создаем статическую переменную, чтобы каждый раз заново не делать запрос
        static $title_id = false;
        if (!$title_id)
        {
            $title_id = $this->select('title,id')->fetchAll('title', 'id');
            $this->select();
        }
        return $this->getByParentId(@$title_id[$parent_title]);
    }
}

?>