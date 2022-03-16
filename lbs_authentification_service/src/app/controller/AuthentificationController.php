<?php

namespace lbs\authentification\app\controller;

use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\authentification\app\models\User;
use lbs\authentification\app\output\Writer;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


class AuthentificationController
{
    private $c;

    public function __construct(\Slim\Container $c)
    {
        $this->c = $c;
    }

    public function authenticate(Request $rq, Response $rs, $args): Response {

        if (!$rq->hasHeader('Authorization')) {

            $rs = $rs->withHeader('WWW-authenticate', 'Basic realm="commande_api api" ');
            return Writer::json_error($rs, 401, 'No Authorization header present');
        };

        //Le client fait 1 demande vers le serveur d'auth. en utilisant l'url et en transmettant les credentials
        $authstring = base64_decode(explode(" ", $rq->getHeader('Authorization')[0])[1]);
        list($email, $pass) = explode(':', $authstring);

        try {
            $user = User::select('id', 'email', 'username', 'passwd', 'refresh_token', 'level')
                ->where('email', '=', $email)
                ->firstOrFail();

            if (!password_verify($pass, $user->passwd))
                throw new \Exception("password check failed");

            unset ($user->passwd);

        } catch (ModelNotFoundException $e) {
            $rs = $rs->withHeader('WWW-authenticate', 'Basic realm="lbs auth" ');
            return Writer::json_error($rs, 401, 'Erreur authentification');
        } catch (\Exception $e) {
            $rs = $rs->withHeader('WWW-authenticate', 'Basic realm="lbs auth" ');
            return Writer::json_error($rs, 401, 'Erreur authentification');
        }

        //   Le serveur vérifie les credentials, génère un access token et un refresh token et les retourne au client
        // $secret = $this->c->settings['secret'];
        // $token = JWT::encode(['iss' => 'http://api.authentification.local/auth',
        //     'aud' => 'http://api.backoffice.local',
        //     'iat' => time(),
        //     'exp' => time() + (12 * 30 * 24 * 3600),
        //     'upr' => [
        //         'email' => $user->email,
        //         'username' => $user->username,
        //         'level' => $user->level
        //     ]],
        //     $secret, 'HS512');

        // $user->refresh_token = bin2hex(random_bytes(32));
        // $user->save();
        // $data = [
        //     'access-token' => $token,
        //     'refresh-token' => $user->refresh_token
        // ];

        // return Writer::json_output($rs, 200, $data);
        $rs->getBody()->write("Succeded");
        return writer::json_output($rs, 200);

    }

}