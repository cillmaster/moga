<?php
/**
 * Действие для отображения и сохранения меню панели администратора
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    administrator
 * @subpackage menu
 */

class pagesMenuAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Меню сайта';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Создаем модель меню
        $model = new pagesMenuModel();
        $menu = $model->getByParentId(0);


        if (isset($_POST['save']))
        {
            $c = count($_POST['level']);
            //Массив, куда будут записаны элементы
            $id_by_level = array(1 => 0);
            for ($i = 0; $i < $c; $i++)
            {
                //Получаем данные
                $level = $_POST['level'][$i];

                $array = array(
                    'id'        => $_POST['id'][$i],
                    'link'      => $_POST['link'][$i],
                    'preg'      => $_POST['preg'][$i],
                    'title'     => $_POST['title'][$i],
                    'link'      => $_POST['link'][$i],
                    'css_class' => $_POST['css_class'][$i],
                    'parent_id' => $id_by_level[$level],
                    'sort'      => $i
                );

                //Если элемент отмечен на удаление
                if ($_POST['delete'][$i])
                {
                    //И id задан, то удаляем
                    if ($array['id'])
                    {
                        $model->deleteById($array['id']);
                    }
                }
                else
                {
                    if (!$array['id'])
                    {
                        unset($array['id']);
                        $model->insert($array);
                        $array['id'] = $model->insertId();
                    } else
                    {
                        //Заменяем
                        $model->replace($array);
                    }
                }
                //Записываем указатель на следующий уровень
                $id_by_level[$level + 1] = $array['id'];
            }
            noxSystem::location(noxSystem::$requestUrl);
        }

        //Добавляем переменные
        $this->addVar('menu', $menu);
    }
}

?>