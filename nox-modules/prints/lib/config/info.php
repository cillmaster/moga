<?php
return array(
    'title'       => 'Чертежи автомобилей',
    'version'     => '1.1',
    'description' => 'Модуль для работы с чертежами',
    'author'      => '<pa-nic@yandex.ru>',
    'frontend'    => true,
    'backend'     => true,
    'rights'      => [
        'blueprint'     => 'Работа с чертежами',
        'vector'        => 'Работа с векторами',
        'request'       => 'Работа с запросами',
        'category'      => 'Работа с категориями/подкатегориями',
    ],
    'menu'        => [
        [
            'link'  => '/administrator/prints/?section=request&action=crm',
            'right' => 'request',
            'title' => 'CRM'
        ],
        [
            'link'  => '/administrator/prints/?section=request',
            'right' => 'request',
            'title' => 'Requests'
        ],
        [
            'link'  => '/administrator/prints/?section=vector',
            'right' => 'vector',
            'title' => 'Vectors',
            'childs' => [
                [
                    'link'  => '/administrator/prints/?section=sets',
                    'right' => 'vector',
                    'title' => 'Sets'
                ],
                [
                    'link'  => '/administrator/prints/?section=models',
                    'right' => 'vector',
                    'title' => 'Models'
                ],
                [
                    'link'  => '/administrator/prints/?section=collections',
                    'right' => 'vector',
                    'title' => 'Collections'
                ]
            ],
        ],
        [
            'link'  => '/administrator/prints/?section=blueprint',
            'right' => 'blueprint',
            'title' => 'Bitmaps'
        ],
        [
            'link'  => '/administrator/prints/?section=classes',
            'right' => 'category',
            'title' => 'Categories'
        ],
        [
            'link'  => '#',
            'right' => 'category',
            'title' => 'Subcategories',
            'childs' => [
                [
                    'link'  => '/administrator/prints/?section=make',
                    'right' => 'blueprint',
                    'title' => 'Make'
                ],
                [
                    'link'  => '/administrator/prints/?section=country',
                    'right' => 'countries',
                    'title' => 'Country'
                ],
            ]
        ],
    ],
);