<?php
return array(
    'title'       => 'Текстовые страницы и Меню сайта',
    'version'     => '1.1',
    'description' => 'Позволяет создавать и управлять текстовыми страницами сайта, а так же меню.',
    'author'      => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
    'rights'      => array('control' => 'Управление модулем'),
    'backend'     => true,
    'frontend'    => true,
    'menu'        => array(
        array('link'   => '/administrator/pages',
              'title'  => 'Страницы сайта',
              'childs' => array(
                  array('link'  => '/administrator/pages/?action=add',
                        'title' => 'Добавить страницу'),
                  array('link'  => '/administrator/pages/?section=menu',
                        'title' => 'Меню')
              )
        )
    ),
    'blocks'      => array('menu' => array('module'  => 'pages',
                                           'section' => 'menu',
                                           'action'  => 'block')
    ),
);
?>