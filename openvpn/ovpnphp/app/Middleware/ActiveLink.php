<?php

namespace App\Middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * ActiveLink
 *
 * @author jrhrmsll
 */
class ActiveLink {

    private $entries = [];

    public function __construct(array $entries) {
        $this->entries = $entries;
    }

    /**
     * check authentication
     * 
     * @param Request $request
     * @param Response $response
     * @param type $next
     */
    public function __invoke(Request $request, Response $response, $next) {
        $_SESSION['active_link'] = '';

        $route = $request->getAttribute('route');

        if (empty($route)) {
            throw new \Slim\Exception\NotFoundException($request, $response);
        }

        $route_name = $route->getName();
        foreach ($this->entries as $entry) {
            if (preg_match("/$entry/", $route_name)) {
                $_SESSION['active_link'] = $entry;
            }
        }

        return $next($request, $response);
    }

}
