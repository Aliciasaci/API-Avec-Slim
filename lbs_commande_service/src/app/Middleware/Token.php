<?php
namespace lbs\command\app\middleware;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Container;
use lbs\command\app\errors\Writer;
use lbs\command\app\models\Commande;
use Illuminate\Database\Eloquent\ModelNotFoundException;


// Le traitement doit procéder à la vérification du token : le système doit vérifier la présence et la valeur du
// token transmis lors de cette requête. On doit prévoir 2 modes de transport du token :
// • transport dans l'url,
// • transport dans un header applicatif.

class Token{

    private $c;

    public function __construct(Container $c){
        $this->c = $c;
    }

    public function check(Request $rq, Response $rs, callable $next){

        //check si le token se trouve dans l'uri
        $token = null;
        $token = $rq->getQueryParams()['token'];

        if(is_null($token)){
            //check si le token se trouve dans un header applicatif
            $api_header = $rq->getHeader('X-lbs-token');
            $token = (isset($api_header[0])? $api_header[0] : null);
        }


        if(empty($token)){
            //garder trace de l'erreur
            ($this->c->get('logger.error')->error("Missing token in Commande route"));
            return Writer::json_error($rs,403,"missing token");
        }
        //Get l'id de la commande se trouvant dans la route
        $commande_id = $rq->getAttribute('route')->getArgument('id');

        $commande = null;
        try{
            $commande = Commande::where('id', '=', $commande_id)->firstOrFail();
            if($commande->token !== $token){
                ($this->c->get('logger.error'))->error("Invalid token in commande route($token)",[$commande->token]);
                return Writer::json_error($rs, 403, "Token invalid");
            }
        
        }
        catch(ModelNotFoundException $e){
            ($this->c->get('logger.error'))->error("unkown commande");
            return Writer::json_error($rs, 404, "Commande inconnue");
        }

        $rq = $rq->withAttribute('command',$commande);
        return $next($rq,$rs);
    }
}