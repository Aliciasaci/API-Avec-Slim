<?php
//Routes de l'API

//use les controller des différents services
use \lbs\command\app\controller\CommandeController;
use \lbs\command\app\controller\Commande_Item_Controller;
use \lbs\command\app\middleware\Middleware;
use \lbs\command\app\middleware\CommandeValidator;
use \lbs\command\app\middleware\Token;
use \DavidePastore\Slim\Validation\Validation as Validation ;

$validators = CommandeValidator::create_validators();

//!peu-être mettre le middleware json à l'objet app directement ?
//Route pour retourner le contenu d'une commande
$app->get('/commandes/{id}[/]',CommandeController::class. ':getCommande')->setName('getCommande')->add(middleware::class. ':putIntoJson')->add(Token::class. ':check');


//Route pour retourner le contenu de toutes les commandes
$app->get('/commandes[/]',CommandeController::class. ':getAllCommande')->setName('getAllCommande')->add(middleware::class. ':putIntoJson')->add(Token::class. ':check');


//Route pour modifier le contenu d'une commande
$app->put('/commandes/{id}[/]',CommandeController::class. ':putCommande')->setName('putCommande')->add(middleware::class. ':putIntoJson');


//Route pour les items d'une commande 
$app->get('/commandes/{id}/items',Commande_Item_Controller::class.':getItems')->setName('getItems')->add(middleware::class. ':putIntoJson')->add(Token::class. ':check');


//Route pour inserer une commande
$app->post('/commandes[/]',CommandeController::class. ':insertCommande')->setName('insertCommande')->add(middleware::class. ':putIntoJson')->add(new Validation($validators));
