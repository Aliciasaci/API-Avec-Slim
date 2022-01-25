<?php

namespace lbs\command\controller;

use \Psr\Http\Message\ServerRequestInterface as Request ;
use \Psr\Http\Message\ResponseInterface as Response ;

class DemoController{
    private $c;         //Le conteneur de dépendance de l'application de

    public function __construct(\Slim\Container $c){
        $this->c = $c;
    }
   
    function commandes(Request $rq, Response $rs, array $args) : response{

        $dbfile = $this->c->settings['db'];
        // $rs=$rs->withHeader("Content-Type","application/json;charset=utf-8");
        $rs->getBody()->write("<h1>HELLO</h1>");
        return $rs;
    }
}

// C:\Users\ASUS\Desktop\Étude\Slim\lbs_commande_service\src\controller\CommandesController.php