<?php
namespace lbs\fab\app\middleware;
use \Slim\Container;

class Middleware{

    private $c;

    public function __construct(Container $c){
        $this->c = $c;
    }

    public function putIntoJson($rq, $rs, callable $next){
        $rs = $rs->withHeader("Content-Type", "application/json;charset=utf-8");
        return $next($rq,$rs);
    }

}