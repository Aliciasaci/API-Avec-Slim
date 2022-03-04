<?php
//Routes de l'API

//use les controller des différents services
use \lbs\command\app\controller\CommandeController;
use \lbs\command\app\controller\Commande_Item_Controller;
use \lbs\command\app\middleware\Middleware;

//Route pour retourner le contenu d'une commande
$app->get('/commandes/{id}[/]',CommandeController::class. ':getCommande')->setName('getCommande')->add(middleware::class. ':putIntoJson');


//Route pour retourner le contenu de toutes les commandes
$app->get('/commandes[/]',CommandeController::class. ':getAllCommande')->setName('getAllCommande')->add(middleware::class. ':putIntoJson');


//Route pour modifier le contenu d'une commande
$app->put('/commandes/{id}[/]',CommandeController::class. ':putCommande')->setName('putCommande')->add(middleware::class. ':putIntoJson');


//Route pour les items d'une commande 
$app->get('/commandes/{id}/items',Commande_Item_Controller::class.':getItems')->setName('getItems')->add(middleware::class. ':putIntoJson');


//Route pour inserer une commande
$app->post('/commandes[/]',CommandeController::class. ':insertCommande')->setName('insertCommande')->add(middleware::class. ':putIntoJson');
