<?php

namespace lbs\fab\app\controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\fab\app\models\Commande;
use lbs\fab\app\output\Writer;

class CommandeController
{
    private $c;

    public function __construct(\Slim\Container $c)
    {
        $this->c = $c;
    }

    // Récuperer toutes les commandes
    public function getAllCommande(Request $req, Response $resp): Response
    {

        //! vérifier que c'est des nuémrique
        $query_filtrage = $req->getQueryParams()['s'] ?? null;
        $query_page = $req->getQueryParams()['page'] ?? 1;
        $query_size = $req->getQueryParams()['size'] ?? 10;


        //le filtrage 
        if ($query_filtrage) {
            $commandes = Commande::select(['id', 'nom', 'montant', 'created_at', 'status'])->where('status', '=', $query_filtrage)->orderBy('livraison', 'DESC')->get();
            $count = count($commandes);
        } else {
            $commandes = Commande::select(['id', 'nom', 'montant', 'created_at', 'status'])->get();
            $count = count($commandes);
        }

        //la pagination et lien des pages
        $nb_page = $count/$query_size;
        if ($query_page) {
            if ($query_page < 0) {
                $commandes = $commandes->take($query_size);
                $size = count($commandes);
            }elseif($query_page > $nb_page){                
                $skip_value = ($nb_page - 1)* $query_size;
                $commandes = $commandes->skip($skip_value)->take($query_size);
                $size = count($commandes);
            }else {
                $skip_value = ($query_page - 1) * $query_size;
                $commandes = $commandes->skip($skip_value)->take($query_size);
                $size = count($commandes);
            }
        }

        $commande_response = [];
        $commande = [];
        foreach ($commandes as $c) {
            //le path d'une commande
            $commandePath = $this->c->router->pathFor(
                'getCommande',
                ['id' => $c->id]
            );

            $commande["commande"] = $c;
            $commande["links"] =  $commandePath;
            array_push($commande_response, $commande);
        }

        $data_resp = [
            "type" => "collection",
            "count" => $count,
            "size" => $size,
            "page" => $query_page,
            "commandes" => $commande_response
        ];

        $resp->getBody()->write(json_encode($data_resp));
        return writer::json_output($resp, 200);

        //! reste à faire le first et last des pages
    }


    public function getCommande(Request $req, Response $resp, array $args): Response
    {
        $id_commande = $args['id'];
        $queries = $req->getQueryParams()['embed'] ?? null;

        try {
            $commande = Commande::select(['id', 'mail', 'nom', 'livraison', 'montant'])
                ->where('id', '=', $id_commande)
                ->firstOrFail();


            //Récuperer la route de la commande en question                           
            $commandePath = $this->c->router->pathFor(
                'getCommande',
                ['id' => $id_commande]
            );

            $CommandeWithItemsPath = $this->c->router->pathFor('getItems', ['id' => $id_commande]);

            // Création des liens hateos
            $hateoas = [
                "items" => ["href" => $CommandeWithItemsPath],
                "self" => ["href" => $commandePath]
            ];

            // Création du body de la réponse
            $datas_resp = [
                "type" => "ressource",
                "commande" => $commande,
                "links" => $hateoas,
            ];

            //*Ressources imbriquées TD4.3
            //GET /commandes/K9J67-4D6F5?embed=items
            if ($queries === 'items') {
                $items = $commande->items()->select('id', 'libelle', 'tarif', 'quantite')->get();
                $datas_resp["commande"]["items"] = $items;
            }

            $resp->getBody()->write(json_encode($datas_resp));
            return writer::json_output($resp, 200);
        } catch (ModelNotFoundException $e) {
            $clientError = $this->c->clientError;
            return $clientError($req, $resp, 404, "Commande not found");
        }
    }
}
