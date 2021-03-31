<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;


/**
 * AuthController
 *
 * @author jrhrmsll
 */
class AuthController {

    private $view;
    private $env_vars;
    private $auth_service;

    public function __construct($view, $env_vars, $auth_service) {
        $this->view = $view;
        $this->env_vars = $env_vars;

        $this->auth_service = $auth_service;
    }

    public function login(Request $request, Response $response) {
        return $this->view->render($response, 'login.twig', []);
    }

    public function signin(Request $request, Response $response) {
        $payload = $request->getParsedBody();
        $username = $payload['username'];
        $password = $payload['password'];
    
        if ($this->auth_service->auth($username, $password)) {
            $_SESSION['auth'] = true;
        } else {
            return $this->view->render($response, 'login.twig', [
                        'errors' => true,
                        'payload' => $payload,
            ]);
        }

        return $response->withStatus(301)->withHeader('Location', '/ovpnphp/');
    }

    public function logout(Request $request, Response $response) {
        $_SESSION['auth'] = false;
        return $response->withStatus(301)->withHeader('Location', '/ovpnphp/login');
    }

}
