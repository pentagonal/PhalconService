<?php
return [
    'environment' => 'development',
    'nameSpace' => [
        'Pentagonal\\Phalcon\\Application\\Web\\Controller' => __DIR__ . '/Resource/Application/Web/Controller/',
    ],
    'includePath' => [
        __DIR__ . '/Resource/Application/Web/Service/_Init.php',
        __DIR__ . '/Resource/Application/Web/Route/_init.php',
    ],
    'cache' => [
        'backend' => [
            'compileAlways' => false
        ]
    ],
    'session' => [
        'name'     => 'session',
        'lifetime' => null,
        'adapter'  => 'files'
    ],
    'database' => [
        "adapter" => "postgresql",
        "host"     => "localhost",
        "username" => "pentagonal",
        "password" => "password",
        "dbname"   => "pentagonal_pl",
    ]
];
