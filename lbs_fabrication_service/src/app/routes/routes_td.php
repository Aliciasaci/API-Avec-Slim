<?php
//Routes de l'API

//use les controller des différents services
use \lbs\fab\app\controller\CommandeController;
use \lbs\fab\app\middleware\Middleware;
// use \lbs\fab\app\middleware\CommandeValidator;
// use \lbs\fab\app\middleware\Token;
// use \DavidePastore\Slim\Validation\Validation as Validation ;

// $validators = CommandeValidator::create_validators();

//Route pour retourner le contenu d'une commande
$app->get('/commandes/{id}[/]',CommandeController::class. ':getCommande')->setName('getCommande')->add(middleware::class. ':putIntoJson');


//Route pour retourner le contenu de toutes les commandes
$app->get('/commandes[/]',CommandeController::class. ':getAllCommande')->setName('getAllCommande')->add(middleware::class. ':putIntoJson');

