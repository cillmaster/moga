<?php
return array(
    'title'       => 'Настройки сайта',
    'version'     => '1.1',
    'description' => 'Позволяет управлять настройками CMS и сайта.',
    'author'      => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
    'fronend'     => false,
    'backend'     => true,
    'rights'      => [
        'control'        => 'Управление сайтом',
        'panel-access'   => 'Доступ в админку'
    ],
    'blocks'      => [
        'admin-menu' =>
            [
                'module'  => 'system',
                'section' => 'menu',
                'action'  => 'block',
            ],
    ],
    'menu'        => array(
        array(
            'link'   => '/administrator/system/?section=config',
            'title'  => 'Система',
            'childs' =>
            array(
                array(
                    'link'   => '/administrator/system/?section=config',
                    'title'  => 'Настройки',
                    'childs' =>
                    array(
                    ),
                ),
                array(
                    'link'   => '/administrator/system/?section=modules',
                    'title'  => 'Модули',
                    'childs' =>
                    array(
                    ),
                ),
                array(
                    'link'   => '/administrator/system/?section=themes',
                    'title'  => 'Темы оформления',
                    'childs' =>
                    array(
                    ),
                ),
                array(
                    'link'   => '/administrator/system/?section=routes',
                    'title'  => 'Маршрутизатор',
                    'childs' =>
                    array(
                    ),
                ),
                array(
                    'link'   => '/administrator/system/?section=db',
                    'title'  => 'Базы данных',
                    'childs' =>
                    array(
                    ),
                ),
                [
                    'link'   => '/administrator/system/?section=oper',
                    'title'  => 'Операции',
                    'childs' => [],
                ],
            ),
        ),
    ),
);