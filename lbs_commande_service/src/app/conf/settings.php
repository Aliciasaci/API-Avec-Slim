<?php

return [
    'settings' => [
        'displayErrorDetails' => true,
        'dbfile'=> __DIR__.'/commandes.config.ini',
        'debug.log'=> __DIR__.'/../log/debug.log',
        'log.level'=> \Monolog\Logger::DEBUG,
        'log.name' => 'slim.log'
    ]
];