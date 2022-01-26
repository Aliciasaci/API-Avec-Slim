<?php

return [
    'dbhost' => function(\Slim\Container $c){
        $config = parse_ini_file($c->settings['dbfile']);
        return $config['host'];
    },

    'logger'=> function(\Slim\Container $c){
        $log = new \Monolog\Logger($c->settings['log.name']);
        $log->pushHandler(new \Monolog\Handler\StreamHandler($c->settings['debug.log'],$c->settings['log.level']));
        return $log;
    },

    // $container['dbfile'] = function ($container) {
    //     $capsule = new \Illuminate\Database\Capsule\Manager;
    //     $capsule->addConnection($container['settings']['dbfile']);
    
    //     $capsule->setAsGlobal();
    //     $capsule->bootEloquent();
    
    //     return $capsule;
    // },
];

