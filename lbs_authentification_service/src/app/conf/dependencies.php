<?php


use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Container;

return [
    'dbhost' => function(Container $c){
        $config = parse_ini_file($c->settings['dbfile']);
        return $config['host'];
    },

    //Logger debug
    'logger.debug' => function(Container $container) {
        $log = new Logger($container->settings['debug.name']);                  //* Nom du log
        $log->pushHandler(new StreamHandler($container->settings['debug.log'],     //* Nom du fichier du log
                                            $container->settings['debug.level'])); //* Niveau de base du log
        return $log;
    },

    // logger warn
    'logger.warn' => function(Container $container) {
        $log = new Logger($container->settings['warn.name']);                  
        $log->pushHandler(new StreamHandler($container->settings['warn.log'],     
                                            $container->settings['warn.level'])); 
        return $log;
    },

     // logger error
     'logger.error' => function(Container  $container) {
        $log = new Logger($container->settings['error.name']);                  
        $log->pushHandler(new StreamHandler($container->settings['error.log'],     
                                            $container->settings['error.level'])); 
        return $log;
    },

];

