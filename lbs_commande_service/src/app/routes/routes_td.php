<?php
//Routes de l'API

//use les controller des différents services
use \lbs\command\app\controller\CommandeController;
use \lbs\command\app\controller\Commande_Item_Controller;



//Route pour retourner le contenu d'une commande
$app->get('/commandes/{id}[/]',CommandeController::class. ':getCommande')->setName('getCommande');


//Route pour retourner le contenu de toutes les commandes
$app->get('/commandes[/]',CommandeController::class. ':getAllCommande')->setName('getAllCommande');


//Route pour modifier le contenu d'une commande
$app->put('/commandes/{id}[/]',CommandeController::class. ':putCommande')->setName('putCommande');


//Route pour les items d'une commande 
$app->get('/commandes/{id}/items',Commande_Item_Controller::class.':getItems')->setName('getItems');