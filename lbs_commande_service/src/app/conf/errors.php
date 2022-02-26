<?php

use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;



return [
    //fonction notFoundHandler déclenche une erreur lorsque la route mise en URL n'est pas défini
    'notFoundHandler' => function (Container $container) {

        return function (Request $req, Response $resp) use ($container): Response {

            //Récupérer l'uri qui est la source de l'erreur
            $uri = $req->getUri();

            //Composer la réponse et mettre son statut à 400
            $resp = $resp->withStatus(400)
                ->withHeader('Content-Type', 'application/json');
            $resp->write(json_encode([
                "type" => 'error',
                "error" => 400,
                "message" => "$uri : Malformed URI - request not recognized"
            ]));

            //Renseigner l'erreur dans le log d'erreur.
            $container->get('logger.error')->error("GET $uri : malformed uri");

            return $resp;
        };
    },


    // fonction not allowed déclenche une erreur dans le cas où l'url de la requête correspond à une route existante mais pas la méthode HTTP
    'notAllowedHandler' => function ($container) {

        // la fonction reçoit la requête, la réponse et un tableau des méthodes autorisées
        return function (Request $req, Response $resp, array $methods) use ($container): Response {

            //Les méthodes autorisées
            $methods_expected = implode(', ', $methods);

            //La méthode reçu 
            $method_received = $req->getMethod();
            $uri             = $req->getUri();

            //composer la réponse, renseigner les méthodes possible et le code d'erreur à 405
            $resp = $resp->withStatus(405)
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Allow', implode(', ', $methods))
                ->write(json_encode([
                    'type'    => 'error',
                    'error'   => 405,
                    'message' => "method $method_received not allowed for uri $uri. Waited : " .
                        $methods_expected
                ]));
        
            //Renseigner l'erreur dans le log d'erreur
            $container->get('logger.error')->error("$method_received $uri : bad method - $methods_expected wanted");

            return $resp;
        };
    },
    
    // fonction phpErrorHandler est declenché lorsqu'on a une erreur PHP dans le script
    'phpErrorHandler' => function ($container) {

        //La fonction reçoit la requête, la réponse et l'exeception à l'origine de l'erreur
        return function (Request $req, Response $resp, \Throwable $error) use ($container): Response {

            //on compose le message de retours
            $msg = [
                'type'    => 'error',
                'error'   => 500,
                'message' => $error->getMessage(),
                'trace'   => $error->getTraceAsString(),
                "file"    => $error->getFile() . "line: " . $error->getLine()
            ];

            $resp = $resp->withStatus(500) //* Internal Server Error
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode($msg));

            unset($msg['trace']);
            $container->get('logger.error')->error(implode(' | ', $msg));

            return $resp;
        };
    },

    //Fonction déclenchée à toute erreur du client exemple : ressources non trouvé,
    'clientError' => function ($container) {
        return function (Request $req, Response $resp, int $code_error, string $msg) use ($container) {
            
            $uri = $req->getUri();

            // Composer le message d'erreur
            $data = [
                'type' => 'error',
                'error' => $code_error,
                'message' => $msg
            ];

            // Les headers d'erreurs 
            $resp = $resp->withStatus($code_error)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');

            $resp->getBody()->write(json_encode($data));

            // renseigner l'erreur dans le log d'erreur
            $container->get('logger.error')->error("bad ressource : " . $uri);

            return $resp;
        };
    }

];