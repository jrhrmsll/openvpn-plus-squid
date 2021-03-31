<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * DatabaseController
 *
 * @author jrhrmsll
 */
class DatabaseController {

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
        return $this->view->render($response, 'tools/index.twig');
    }

    public function reset(Request $request, Response $response) {
        $this->resetDatabase();

        $this->flash->addMessage('info', 'Database was reset successfully.');

        return $response->withStatus(301)->withHeader('Location', '/ovpnphp/clients');
    }

    public function notification(Request $request, Response $response) {
        $payload = $request->getParsedBody();
        $action = $payload['action'];

        switch ($action) {
            case 'init':
                $this->resetDatabase();
                break;

            case 'create':
                $common_name = $payload['common_name'];
                $this->cert_repository->save($this->ovpnapi->certInfo($common_name));
                break;

            case 'revoke':
                $common_name = $payload['common_name'];
                $this->cert_repository->update($this->ovpnapi->certInfo($common_name));
                break;

            case 'updatedb':
                foreach ($this->cert_repository->findExpiredCerts() as $common_name) {
                    print_r($this->ovpnapi->certInfo($common_name));
                    $this->cert_repository->update($this->ovpnapi->certInfo($common_name));
                }
                break;

            default:
                break;
        }
    }

    private function resetDatabase() {
        $this->cert_repository->deleteAll();

        $cert_list = $this->ovpnapi->certList();

        foreach ($cert_list as $filename) {
            $common_name = str_replace(".crt", "", $filename);
            $this->cert_repository->save($this->ovpnapi->certInfo($common_name));
        }
    }

}
