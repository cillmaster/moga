<?php return
[
    'title'       => 'Система пользователей',
    'version'     => '1.1',
    'description' => 'Обеспечивает авторизацию пользователей, регистрацию и управление в панели администратора.',
    'author'      => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
    'rights'      => [
        'users'  => 'Урпавление пользователями',
        'groups' => 'Управление группами'
    ],
    'backend'     => true,
    'frontend'    => true,
    'menu'        => [
        [
            'link'   => '/administrator/users',
            'title'  => 'Пользователи',
            'right' => 'users',
            'childs' => [
                [
                    'link'  => '/administrator/users',
                    'title' => 'Пользователи',
                    'right' => 'users',
                ],
                [
                    'link'  => '/administrator/users/?section=groups',
                    'title' => 'Группы пользователей',
                    'right' => 'groups'
                ]
            ]
        ]
    ],
    'blocks'      => [
        'user-login' => [
            'module'  => 'users',
            'section' => 'default',
            'action'  => 'loginBlock'
        ]
    ]
];