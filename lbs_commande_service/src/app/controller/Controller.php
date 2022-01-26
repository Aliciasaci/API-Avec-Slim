<?php

namespace lbs\command\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request ;
use \Psr\Http\Message\ResponseInterface as Response ;

class Controller{
    private $c;    

    public function __construct(\Slim\Container $c){
        $this->c = $c;
    }
   
    function commandes(Request $rq, Response $rs, array $args) : response{

        $dbfile = $this->c->settings['dbfile'];
        $rs->getBody()->write("<h1>Toutes les commandes $dbfile</h1>");
        return $rs;
    }
    function oneCommande(Request $rq, Response $rs, array $args) : response{
        $id = $args['id'];
        $dbfile = $this->c->settings['dbfile'];
        $rs->getBody()->write("<h1>retourne une seule commandes dont l'id est $id</h1>");
        return $rs;
    }
}

// C:\Users\ASUS\Desktop\Étude\Slim\lbs_commande_service\src\controller\CommandesController.php