<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * ServerController
 *
 * @author jrhrmsll
 */
class ServerController {

    private $view;
    private $flash;
    private $ovpnapi;
    private $cert_repository;
    private $env_vars;

    public function __construct($view, $flash, $ovpnapi, $cert_repository, $env_vars) {
        $this->view = $view;
        $this->flash = $flash;
        $this->ovpnapi = $ovpnapi;
        $this->cert_repository = $cert_repository;
        $this->env_vars = $env_vars;
    }

    public function index(Request $request, Response $response) {
        $version = str_replace('OpenVPN Version:', '', $this->ovpnapi->version());
        $virtual_address = $this->ovpnapi->serverVirtualAddress();

        $stats = $this->cert_repository->clientCertsStats();
        $stats['Connected'] = $this->ovpnapi->connections();

        return $this->view->render($response, 'server/index.twig', [
                    'version' => $version,
                    'virtual_address' => $virtual_address,
                    'env_vars' => $this->env_vars,
                    'stats' => $stats,
        ]);
    }

}
