<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * Auth
 *
 * @author jrhrmsll
 */
class Auth {

    /**
     * check authentication
     * 
     * @param Request $request
     * @param Response $response
     * @param type $next
     */
    public function __invoke(Request $request, Response $response, $next) {
        $route = $request->getAttribute('route');

        if (empty($route)) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }

        if (preg_match('/login|signin|notification/', $route->getName()) || $_SESSION['auth'] === true) {
            return $next($request, $response);
        } else {
            return $response->withStatus(301)->withHeader('Location', '/ovpnphp/login');
        }
    }

}
