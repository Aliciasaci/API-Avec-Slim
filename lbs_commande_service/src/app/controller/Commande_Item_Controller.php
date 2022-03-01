<?php
namespace lbs\command\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\command\app\models\Commande;
use lbs\command\app\errors\Writer;

class Commande_Item_Controller{
    private $c;

    public function __construct(\Slim\Container $c)
    {
        $this->c = $c;
    }

    //TD4.1 associations : récuperer les items correspondant à une commande
    //GET /commandes/{id}/items
    public function getItems(Request $req, Response $resp, array $args): Response
    {
        $id_commande = $args['id'];
        $commande = Commande::findOrFail($id_commande);
        $count_items = count($commande->items);
        $items = $commande->items()->select('id','libelle','tarif','quantite')->get();


        //Construire la réponse
        $reponse = [
            "type" => "collection",
            "count" => $count_items,
            "items" => $items
            ];

        
        $resp = Writer::json_output($resp,200);
        $resp = $resp->withHeader("Content-Type", "application/json;charset=utf-8");
        $resp->getBody()->write(json_encode($reponse));
        return $resp;
    }

    //*rajouter un message dexception erreur si commande non trouvé
}