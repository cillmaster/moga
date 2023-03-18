<?php
return array(
    'title'       => 'Оплата',
    'version'     => '1.0',
    'description' => 'Модуль для работы с платежами',
    'author'      => 'pa-nic@yandex.ru',
    'frontend'    => true,
    'backend'     => true,

    'rights'      => [
        'control'    => 'Полное управление модулем'
    ],

    'menu'        => [
        [
            'link'  => '/administrator/payment/?section=payment',
            'right' => 'control',
            'title' => 'Sales',
        ]
    ],
);