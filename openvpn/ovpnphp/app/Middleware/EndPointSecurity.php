<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * EndPointSecurity
 *
 * @author jrhrmsll
 */
class EndPointSecurity {

    /**
     * check IP Address
     * 
     * @param Request $request
     * @param Response $response
     * @param type $next
     * @throws \Slim\Exception\NotFoundException
     */
    public function __invoke(Request $request, Response $response, $next) {
        $route = $request->getAttribute('route');

        if (empty($route)) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }

        $reliable = [
            'localhost',
            '127.0.0.1',
            '::1'
        ];

        $ip_address = $request->getAttribute('ip_address');
        if (preg_match('/notification/', $route->getName()) && array_search($ip_address, $reliable) !== FALSE) {
            return $next($request, $response);
        } else {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }
    }

}
