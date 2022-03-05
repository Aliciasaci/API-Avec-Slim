<?php

//! Nettoyer le code !!!!!!!!!!!!!!!!!!!!!!
namespace lbs\command\app\controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\command\app\models\Commande;
use lbs\command\app\models\Item;
use lbs\command\app\errors\Writer;
use DateTime;
use Ramsey\Uuid\Uuid;

class CommandeController
{
    private $c;

    public function __construct(\Slim\Container $c)
    {
        $this->c = $c;
    }

    public function insertCommande(Request $req, Response $resp, array $args): Response
    {

        // $clientError = $this->c->clientError;
        //Les données reçues pour la nouvelle commande
        $received_commande = $req->getParsedBody();

        //! comment traiter le message de retours dans ce cas ? un json_error ou pas ? 
        //! quelle est l'erreur adéquate dans ce cas ? 403 ? 
        //! dois-je customiser les messages d'erreur ?
        if ($req->getAttribute('has_errors')) {

            $errors = $req->getAttribute('errors');

            if (isset($errors['nom_client'])) {
                ($this->c->get('logger.error'))->error("error",$errors['nom_client']);
                return Writer::json_error($resp, 403, "Le champ 'nom_client' ne doit pas être vide et doit contenir que des lettres");
            }
            if (isset($errors['mail_client'])) {
                ($this->c->get('logger.error'))->error("error",$errors['mail_client']);
                return Writer::json_error($resp, 403, "Le champ 'mail_client' ne doit pas être vide et doit être valide");
            }
            if (isset($errors['livraison.date'])) {    //une vérification à faire sur l'heure ? 
                ($this->c->get('logger.error'))->error("error",$errors['livraison.date']);
                return Writer::json_error($resp, 403, "La date de livraison ne doit pas être inférieur à la date d'aujourd'hui et doit être le format d-m-Y");
            }
            if (isset($errors['items'])) {
                ($this->c->get('logger.error'))->error("error",$errors['items']);
                return Writer::json_error($resp, 403, "le champ items ne doit pas être vide et toutes les informations doivent être valide");
            }
        } else {

            //Création du token unique et cryptographique
            $token_commande = random_bytes(32);
            $token_commande = bin2hex($token_commande);

            //créer la commande et son id
            $new_commande = new Commande();
            $new_commande_id = Uuid::uuid4();
            $new_commande->id =  $new_commande_id;


            //Récuperer le tableau des items 
            $items = $received_commande['items'];
            $montant_total = 0;
            foreach ($items as $item) {
                $new_item = new Item();
                $new_item->uri = $item['uri'];
                $new_item->quantite = $item['q'];
                $new_item->libelle = $item['libelle'];
                $new_item->tarif = $item['tarif'];
                $new_item->command_id = $new_commande_id;
                $montant_total += $item['tarif'];
                $new_item->save();
            }

            $new_commande->nom = filter_var($received_commande['nom_client'], FILTER_SANITIZE_STRING);
            $new_commande->mail = filter_var($received_commande['mail_client'], FILTER_SANITIZE_EMAIL);
            $temp_livraison_date = new  DateTime($received_commande['livraison']['date'] . ' ' . $received_commande['livraison']['heure']);
            $new_commande->livraison = $temp_livraison_date->format('Y-m-d H:i:s');
            $new_commande->montant =  $montant_total;
            $new_commande->token = $token_commande;
            $new_commande->save();


            // Récupération du path pour le location dans header
            $path_commande = $this->c->router->pathFor(
                'getCommande',
                ['id' => $new_commande->id]
            );

            //Construire la réponse : 
            $response = [
                "type" => "ressource",
                "commande" => $new_commande,
            ];

            //Le retour
            $resp->getBody()->write(json_encode($response));
            $resp->withHeader('X-lbs-token', $new_commande->token);
            return writer::json_output($resp, 201)->withHeader("location", $path_commande);
        }

        //!Voir avec l'exception à catch ici
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

        $resp->getBody()->write(json_encode($data_resp));
        return writer::json_output($resp, 200);
    }




    //Récuperer une seule commande par id
    public function getCommande(Request $req, Response $resp, array $args): Response
    {
        //L'id de la commande est passé en argument
        $id_commande = $args['id'];
        //récuper les paramètre passés en URI
        $queries = $req->getQueryParams()['embed'] ?? null;

        try {
            // $commande = Commande::select(['id', 'nom', 'mail', 'montant'])
            //                     ->where('id', '=', $id_commande)
            //                     ->firstOrFail();

            //* Modification TD4.2
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

            //Vas voir dans les cours de server web pour voir avc quoi remplacer this
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


//on ajoute le middlewear check qui est dans la classe token dans le dossier middlewear quand on reup les items, modifier une commande, payer une commande, get la commande.
