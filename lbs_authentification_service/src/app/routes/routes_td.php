<?php
//Routes de l'API

//use les controller des différents services
use \lbs\authentification\app\controller\AuthentificationController;
use \lbs\authentification\app\middleware\Middleware;


$app->post('/auth[/]',AuthentificationController::class. ':authenticate')->setName('authenticate')->add(middleware::class. ':putIntoJson');

$app->get('/access[/]',AuthentificationController::class. ':checkToken')->setName('checkToken')->add(middleware::class. ':putIntoJson');
