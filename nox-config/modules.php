<?php
return [
    'download' => [
        'title' => 'Система скачивания файлов',
        'version' => '1.0',
        'description' => '',
        'author' => '<pa-nic@yandex.ru>',
        'rights' => [
            'control' => 'Управление модулем'
        ],
        'frontend' => true,
        'backend' => false,
        'install_date' => '2015-03-05 08:40:43',
    ],
    'error' =>[
        'name' => 'error',
        'title' => 'Обработчик ошибок',
        'version' => '1.0',
        'description' => 'Определяет внешний вид сообщений об ошибках.',
        'author' => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
        'install_date' => '2012-06-14 12:39:31',
    ],
    'filemanager' =>[
        'title' => 'Файловый менеджер',
        'version' => '1.1',
        'description' => 'Позволяет управлять файловой системой сайта и редактировать файлы.',
        'author' => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
        'rights' =>[
            'data' => 'Доступ к папке nox-data',
            'upload' => 'Загрузка файлов',
            'all' => 'Доступ ко всем файлам',
        ],
        'frontend' => false,
        'backend' => true,
        'menu' => [],
        'install_date' => '2015-05-14 17:15:42',
    ],
    'landings' => [
        'title' => 'Лендинги',
        'version' => '1.0',
        'description' => 'Лендинги',
        'author' => '',
        'rights' => [
            'control' => 'Управление модулем',
        ],
        'frontend' => true,
        'backend' => true,
        'menu' => [],
        'install_date' => '2015-05-14 17:14:15',
    ],
    'log' => [
        'title' => 'Лог сайта',
        'version' => '1.0',
        'description' => 'Записывает действия пользователя в админке',
        'author' => '<pa-nic@yandex.ru>',
        'frontend' => false,
        'backend' => true,
        'rights' => [
            'control' => 'Просмотр статистики',
        ],
        'menu' => [],
        'install_date' => '2015-05-14 17:13:01',
    ],
    'main' => [
        'title' => 'Главный модуль сайта',
        'version' => '1.0',
        'description' => 'Обеспечивает работу служебных страниц.',
        'author' => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
        'frontend' => true,
        'backend' => false,
        'install_date' => '2012-12-24 14:55:52',
    ],
    'pages' => [
        'title' => 'Текстовые страницы и Меню сайта',
        'version' => '1.1',
        'description' => 'Позволяет создавать и управлять текстовыми страницами сайта, а так же меню.',
        'author' => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
        'rights' => [
            'control' => 'Управление модулем',
        ],
        'backend' => true,
        'frontend' => true,
        'menu' => [
            [
                'link' => '/administrator/pages',
                'title' => 'Страницы сайта',
                'childs' => [
                    [
                        'link' => '/administrator/pages/?action=add',
                        'title' => 'Добавить страницу',
                    ], [
                        'link' => '/administrator/pages/?section=menu',
                        'title' => 'Меню',
                    ],
                ],
            ],
        ],
        'blocks' => [
            'menu' => [
                'module' => 'pages',
                'section' => 'menu',
                'action' => 'block',
            ],
        ],
        'install_date' => '2012-12-20 16:22:07',
    ],
    'payment' => [
        'title' => 'Оплата',
        'version' => '1.0',
        'description' => 'Модуль для работы с платежами',
        'author' => '<pa-nic@yandex.ru>',
        'frontend' => true,
        'backend' => true,
        'rights' => [
            'sales' => 'Полный доступ к информации по продажам',
            'preorders' => 'Доступ только к информации по предзаказам',
        ],
        'menu' => [
            [
                'link' => '/administrator/payment/?section=payment',
                'right' => 'sales',
                'title' => 'Sales',
            ], [
                'link' => '/administrator/payment/?section=preorder',
                'right' => 'preorders',
                'title' => 'Pre-orders',
            ],
        ],
        'install_date' => '2015-05-14 17:15:50',
    ],
    'prints' =>
        array (
            'title' => 'Чертежи автомобилей',
            'version' => '1.1',
            'description' => 'Модуль для работы с чертежами',
            'author' => '<pa-nic@yandex.ru>',
            'frontend' => true,
            'backend' => true,
            'rights' =>
                array (
                    'blueprint' => 'Работа с чертежами',
                    'vector' => 'Работа с векторами',
                    'request' => 'Работа с запросами',
                    'requestCRM' => 'Работа с запросами CRM',
                    'category' => 'Работа с категориями/подкатегориями',
                ),
            'menu' =>
                array (
                    0 =>
                        array (
                            'link' => '/administrator/prints/?section=request&action=crm&status=1&update_count=0&days=90&want_pay=3',
                            'right' => 'requestCRM',
                            'title' => 'CRM',
                        ),
                    1 =>
                        array (
                            'link' => '/administrator/prints/?section=request&status=1',
                            'right' => 'request',
                            'title' => 'Requests',
                        ),
                    2 =>
                        [
                            'link' => '/administrator/prints/?section=vector',
                            'right' => 'vector',
                            'title' => 'Vectors',
                            'childs' => [
                                [
                                    'link' => '/administrator/prints/?section=sets',
                                    'right' => 'vector',
                                    'title' => 'Sets',
                                ], [
                                    'link' => '/administrator/prints/?section=models',
                                    'right' => 'vector',
                                    'title' => 'Models',
                                ], [
                                    'link' => '/administrator/prints/?section=collections',
                                    'right' => 'vector',
                                    'title' => 'Collections',
                                ]
                            ]
                        ],
                    3 =>
                        array (
                            'link' => '/administrator/prints/?section=blueprint',
                            'right' => 'blueprint',
                            'title' => 'Bitmaps',
                        ),
                    4 =>
                        array (
                            'link' => '/administrator/prints/?section=classes',
                            'right' => 'category',
                            'title' => 'Categories',
                        ),
                    5 =>
                        array (
                            'link' => '#',
                            'right' => 'category',
                            'title' => 'Subcategories',
                            'childs' =>
                                array (
                                    0 =>
                                        array (
                                            'link' => '/administrator/prints/?section=make',
                                            'right' => 'blueprint',
                                            'title' => 'Make',
                                        ),
                                    1 =>
                                        array (
                                            'link' => '/administrator/prints/?section=country',
                                            'right' => 'countries',
                                            'title' => 'Country',
                                        ),
                                ),
                        ),
                ),
            'install_date' => '2015-06-14 13:02:46',
        ),
    'system' =>
        array (
            'title' => 'Настройки сайта',
            'version' => '1.1',
            'description' => 'Позволяет управлять настройками CMS и сайта.',
            'author' => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
            'frontend' => false,
            'backend' => true,
            'rights' =>
                array (
                    'control' => 'Управление сайтом',
                    'panel-access' => 'Доступ в админку',
                ),
            'blocks' =>
                array (
                    'admin-menu' =>
                        array (
                            'module' => 'system',
                            'section' => 'menu',
                            'action' => 'block',
                        ),
                ),
            'menu' =>
                array (
                    0 =>
                        array (
                            'link' => '/administrator/system/?section=config',
                            'title' => 'Система',
                            'childs' =>
                                array (
                                    0 =>
                                        array (
                                            'link' => '/administrator/system/?section=config',
                                            'title' => 'Настройки',
                                            'childs' =>
                                                array (
                                                ),
                                        ),
                                    1 =>
                                        array (
                                            'link' => '/administrator/system/?section=modules',
                                            'title' => 'Модули',
                                            'childs' =>
                                                array (
                                                ),
                                        ),
                                    2 =>
                                        array (
                                            'link' => '/administrator/system/?section=themes',
                                            'title' => 'Темы оформления',
                                            'childs' =>
                                                array (
                                                ),
                                        ),
                                    3 =>
                                        array (
                                            'link' => '/administrator/system/?section=routes',
                                            'title' => 'Маршрутизатор',
                                            'childs' =>
                                                array (
                                                ),
                                        ),
                                    4 =>
                                        array (
                                            'link' => '/administrator/system/?section=db',
                                            'title' => 'Базы данных',
                                            'childs' =>
                                                array (
                                                ),
                                        ),
                                    5 =>
                                        [
                                            'link' => '/administrator/system/?section=oper',
                                            'title' => 'Операции',
                                            'childs' => [],
                                        ],
                                ),
                        ),
                ),
            'install_date' => '2015-05-14 17:15:57',
        ),
    'tag' =>
        array (
            'title' => 'Система тегов',
            'version' => '1.0',
            'description' => 'Модуль для работы с тегами',
            'author' => '<pa-nic@yandex.ru>',
            'frontend' => true,
            'backend' => true,
            'rights' =>
                array (
                    'control' => 'Полное управление модулем',
                ),
            'menu' =>
                array (
                ),
            'install_date' => '2015-05-14 17:12:54',
        ),
    'users' =>
        array (
            'title' => 'Система пользователей',
            'version' => '1.0',
            'description' => 'Обеспечивает авторизацию пользователей, регистрацию и управление в панели администратора.',
            'author' => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
            'rights' =>
                array (
                    'users' => 'Урпавление пользователями',
                    'groups' => 'Управление группами',
                ),
            'backend' => true,
            'frontend' => true,
            'menu' =>
                array (
                    0 =>
                        array (
                            'link' => '/administrator/users',
                            'title' => 'Пользователи',
                            'right' => 'users',
                            'childs' =>
                                array (
                                    0 =>
                                        array (
                                            'link' => '/administrator/users',
                                            'title' => 'Пользователи',
                                            'right' => 'users',
                                        ),
                                    1 =>
                                        array (
                                            'link' => '/administrator/users/?section=groups',
                                            'title' => 'Группы пользователей',
                                            'right' => 'groups',
                                        ),
                                ),
                        ),
                ),
            'blocks' =>
                array (
                    'user-login' =>
                        array (
                            'module' => 'users',
                            'section' => 'default',
                            'action' => 'loginBlock',
                        ),
                ),
            'install_date' => '2015-05-14 17:16:03',
        ),
    'utm' =>
        array (
            'title' => 'utm метки',
            'version' => '1.0',
            'description' => 'utm метки',
            'author' => '<pa-nic@yandex.ru>',
            'frontend' => true,
            'backend' => true,
            'rights' =>
                array (
                    'control' => 'Полное управление модулем',
                ),
            'menu' =>
                array (
                ),
            'install_date' => '2015-05-14 17:12:49',
        ),
    'reports' => [
        'title' => 'Отчеты, статистика',
        'version' => '0.1',
        'description' => 'Позволяет выводить сводную информацию из базы данных.',
        'author' => '<admin@jscript.pro>',
        'frontend' => false,
        'backend' => true,
        'rights' => [
            'control' => 'Полное управление модулем'
        ],
        'menu' => [
            [
                'link' => '/administrator/reports/?report_id=map',
                'title' => 'Отчеты Stats',
                'childs' => []
            ]
        ]
    ],
]
?>