<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * ConnectionsController
 *
 * @author jrhrmsll
 */
class ConnectionsController {

    private $view;
    private $flash;
    private $ovpnapi;
    private $cert_repository;

    public function __construct($view, $flash, $ovpnapi, $cert_repository) {
        $this->view = $view;
        $this->flash = $flash;
        $this->ovpnapi = $ovpnapi;
        $this->cert_repository = $cert_repository;
    }

    public function index(Request $request, Response $response) {
        $clients = $this->ovpnapi->status();

        return $this->view->render($response, 'connections/index.twig', [
                    'clients' => $clients,
                    'flashes' => $this->flash->getMessages()
        ]);
    }

    public function delete(Request $request, Response $response, $args) {
        $name = $args['name'];

        if ($this->ovpnapi->killClient($name)) {
            $this->flash->addMessage('notice', 'The client was disconnected successfully.');
        } else {
            $this->flash->addMessage('error', 'Client are disconnected.');
        }

        return $response->withStatus(301)->withHeader('Location', '/ovpnphp/connections');
    }

    public function show(Request $request, Response $response, $args) {
        $name = $args['name'];

        $status = $this->ovpnapi->clientStatus($name);
        $cert = $this->cert_repository->findByCommonName($name);

        unset($status['Common Name']);
        unset($status['Connected Since (time_t)']);
        unset($status['Username']);

        return $this->view->render($response, 'connections/show.twig', [
                    'status' => $status,
                    'cert' => $cert
        ]);
    }

}
