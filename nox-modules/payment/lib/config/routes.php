<?php return
[
    [
        'url'     => 'buy/<what>/<id>',
        'section' => 'payment',
        'action'  => 'startBuy',
        'enabled' => true
    ],
    [
        'url'     => 'cart',
        'section' => 'cart',
        'action'  => 'cart',
        'enabled' => true
    ],
    [
        'url'     => 'cartBuy',
        'section' => 'payment',
        'action'  => 'startCartBuy',
        'enabled' => true
    ],
    [
        'url'     => 'cartCmd',
        'section' => 'cart',
        'action'  => 'cartCmd',
        'enabled' => true
    ],
    [
        'url'     => 'cartDetails',
        'section' => 'cart',
        'action'  => 'cartDetails',
        'enabled' => true
    ],
    [
        'url'     => 'finish',
        'section' => 'payment',
        'action'  => 'saleFinish',
        'enabled' => true
    ],

];
