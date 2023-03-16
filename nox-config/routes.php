<?php
return [
    'default' =>
        [
            [
                'url' => 'suggestions/*',
                'module' => 'away',
                'enabled' => '1',
            ], [
                'url' => 'events/*',
                'module' => 'away',
                'enabled' => '1',
            ], [
                'url' => 'users/*',
                'module' => 'users',
                'enabled' => '1',
            ], [
                'url' => 'gallery/*',
                'module' => 'away',
                'enabled' => '1',
            ], [
                'url' => 'checkout/*',
                'module' => 'payment',
                'enabled' => '1',
            ], [
                'url' => 'payments/*',
                'module' => 'payment',
                'enabled' => '1',
            ], [
                'url' => 'download/*',
                'module' => 'download',
                'enabled' => '1',
            ], [
                'url' => '*',
                'module' => 'prints',
                'enabled' => '1',
            ], [
                'url' => '*',
                'module' => 'main',
                'enabled' => '1',
            ], [
                'url' => '*',
                'module' => 'landings',
                'enabled' => '1',
            ], [
                'url' => '*',
                'module' => 'away',
                'enabled' => '1',
            ], [
                'url' => '*',
                'module' => 'pages',
                'enabled' => '1',
            ],
        ],
]
?>