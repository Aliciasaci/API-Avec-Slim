<?php

namespace lbs\command\app\controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\command\app\models\Commande;
use lbs\command\app\errors\Writer;
use DateTime;

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

        $commandes = Commande::select(['id', 'nom', 'mail', 'montant'])->get();

        // Construction des donnés à retourner dans le corps de la réponse
        $data_resp = [
            "type" => "collection",
            "count" => count($commandes),
            "commandes" => $commandes
        ];

        //Écriture des headers de retours
        $resp = $resp->withStatus(200);
        $resp = $resp->withHeader('application-header', 'TD 1 _ Commandes');
        $resp = $resp->withHeader("Content-Type", "application/json;charset=utf-8");

        $resp->getBody()->write(json_encode($data_resp));

        return $resp;
    }


    //Récuperer une seule commande par id
    public function getCommande(Request $req, Response $resp, array $args): Response
    {
        //L'id de la commande est passé en argument
        $id_commande = $args['id'];
        //récuper les paramètre passés en URI
        $queries = $req->getQueryParams() ?? null;

        try {
            // $commande = Commande::select(['id', 'nom', 'mail', 'montant'])
            //                     ->where('id', '=', $id_commande)
            //                     ->firstOrFail();

            //* Modification TD4.2
            $commande = Commande::select(['id', 'mail', 'nom', 'created_at', 'livraison', 'montant'])
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
            if ($queries['embed'] === 'items') {
                $items = $commande->items()->select('id', 'libelle', 'tarif', 'quantite')->get();
                $datas_resp["commande"]["items"] = $items;
            }

            $resp = $resp->withStatus(200);
            $resp = $resp->withHeader('application-header', 'TD 1 _ Commandes');
            $resp = $resp->withHeader("Content-Type", "application/json;charset=utf-8");


            $resp->getBody()->write(json_encode($datas_resp));

            return $resp;
        } catch (ModelNotFoundException $e) {

            $clientError = $this->c->clientError;
            return $clientError($req, $resp, 404, "Commande not found");
        }
    }

    //Modifier une commande
    public function putCommande(Request $req, Response $resp, array $args): Response
    {

        //commande_data correspond aux données de la nouvelle commande.
        $commande_data = $req->getParsedBody();

        $clientError = $this->c->clientError;

        if (!isset($commande_data['nom_client'])) {
            return $clientError($req, $resp, 400, "Missing 'nom_client");
            // return Writer::json_error($resp, 400, "missing 'nom_client'");
        };

        if (!isset($commande_data['mail_client'])) {
            return Writer::json_error($resp, 400, "missing 'mail_client'");
        };

        if (!isset($commande_data['livraison']['date'])) {
            return Writer::json_error($resp, 400, "missing 'livraison(date)'");
        };

        if (!isset($commande_data['livraison']['heure'])) {
            return Writer::json_error($resp, 400, "missing 'livraison(heure)'");
        };

        try {
            // Récupérer la commande
            $commande = Commande::Select(['id', 'nom', 'mail', 'livraison'])->findOrFail($args['id']);

            $commande->nom = filter_var($commande_data['nom_client'], FILTER_SANITIZE_STRING);
            $commande->mail = filter_var($commande_data['mail_client'], FILTER_SANITIZE_EMAIL);
            $commande->livraison = DateTime::createFromFormat(
                'Y-m-d H:i',
                $commande_data['livraison']['date'] . ' ' .
                    $commande_data['livraison']['heure']
            );

            $commande->save();

            //Code de succès 
            return Writer::json_output($resp, 204);
        } catch (ModelNotFoundException $e) {
            return Writer::json_error($resp, 404, "commande inconnue : {$args}");
        } catch (\Exception $e) {
            return Writer::json_error($resp, 500, $e->getMessage());
        }

        return $resp;
    }
}
// C:\Users\ASUS\Desktop\Étude\Slim\lbs_commande_service\src\controller\CommandesController.php