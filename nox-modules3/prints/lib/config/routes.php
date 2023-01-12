<?php return
[
    [
        'url'     => '__debug__kk',
        'section' => 'debug',
        'action'  => 'debug',
        'enabled' => true
    ],
    // From .info
    [
        'url'     => 'cbr.info/b/<id>',
        'section' => 'old',
        'action'  => 'bpRedirect',
        'enabled' => true
    ],

    // Старые маршруты для текущей совместимости
    [
        'url'     => 'requests/<id>/<url>-vector-drawings',
        'section' => 'request',
        'action'  => 'vectorOLD',
        'enabled' => true
    ],

    //Blueprint
    [
        'url'     => 'blueprints',
        'section' => 'blueprints',
        'action'  => 'default',
        'enabled' => true
    ],
    [
        'url'     => 'blueprints/<printId>/<printUrl>-blueprints',
        'section' => 'blueprints',
        'action'  => 'print',
        'enabled' => true
    ],
    [
        'url'     => '<category>-blueprints',
        'section' => 'blueprints',
        'action'  => 'category',
        'enabled' => true
    ],
    [
        'url'     => '<category>-blueprints/<subcategory>',
        'section' => 'blueprints',
        'action'  => 'subcategory',
        'enabled' => true
    ],

    //Vector
    [
        'url'     => 'vector-drawings',
        'section' => 'vector',
        'action'  => 'default',
        'enabled' => true
    ],
    [
        'url'     => 'vector-drawings/<vectorId>/<vectorUrl>',
        'section' => 'vector',
        'action'  => 'print',
        'enabled' => true
    ],
    [
        'url'     => '<category>-vector-drawings',
        'section' => 'vector',
        'action'  => 'category',
        'enabled' => true
    ],
    [
        'url'     => '<category>-vector-drawings/<subcategory>',
        'section' => 'vector',
        'action'  => 'subcategory',
        'enabled' => true
    ],

    //Search
    [
        'url'     => 'search',
        'section' => 'search',
        'action'  => 'default',
        'enabled' => true
    ],
    [
        'url'     => 'hint',
        'section' => 'search',
        'action'  => 'hint',
        'enabled' => true
    ],

    //Request
    [
        'url'     => 'requests',
        'section' => 'request',
        'action'  => 'default',
        'enabled' => true
    ],
    [
        'url'     => 'requests/search',
        'section' => 'request',
        'action'  => 'search',
        'enabled' => true
    ],
    [
        'url'     => 'requests/filter',
        'section' => 'request',
        'action'  => 'filter',
        'enabled' => true
    ],
    [
        'url'     => 'requests/options',
        'section' => 'make',
        'action'  => 'options',
        'enabled' => true
    ],
    [
        'url'     => 'requests/<id>/ajax/vote',
        'section' => 'request',
        'action'  => 'vectorVote',
        'enabled' => true
    ],
    [
        'url'     => 'requests/<id>/<url>',
        'section' => 'request',
        'action'  => 'vector',
        'enabled' => true
    ],
    [
        'url'     => 'requests/create/vector/from/blueprint/<blueprintId>',
        'section' => 'request',
        'action'  => 'createFromBlueprint',
        'enabled' => true
    ],
    //Collection
    [
        'url'     => 'collections/<collectionUrl>',
        'section' => 'collection',
        'action'  => 'default',
        'enabled' => true
    ],
    //Sets
    [
        'url'     => 'sets/<setUrl>',
        'section' => 'set',
        'action'  => 'set',
        'enabled' => true
    ],

    //HomePage
    [
        'url'     => '',
        'section' => 'default',
        'action'  => 'default',
        'enabled' => true
    ],
];