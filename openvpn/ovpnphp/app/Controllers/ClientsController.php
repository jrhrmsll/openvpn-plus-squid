<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Respect\Validation\Validator as v;

/**
 * ClientsController
 *
 * @author jrhrmsll
 */
class ClientsController {

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
        $clients = $this->cert_repository->findClients();

        return $this->view->render($response, 'clients/index.twig', [
                    'clients' => $clients,
                    'flashes' => $this->flash->getMessages(),
        ]);
    }

    public function create(Request $request, Response $response) {
        return $this->view->render($response, 'clients/create.twig', [
                    'flashes' => $this->flash->getMessages(),
        ]);
    }

    public function store(Request $request, Response $response) {
        $payload = $request->getParsedBody();

        $name = $payload['name'];
        $server_ip = $payload['server_ip'];
        $custom_client_ip = $payload['custom_client_ip'];

        $errors = $this->validateCreate($payload);

        if (!count($errors)) {
            $result = $this->ovpnapi->genClientCert($name);

            if (substr_count($result, 'Data Base Updated')) {
                $this->ovpnapi->genClientConfig($name, $server_ip);
                $custom_client_ip !== '' ? $this->ovpnapi->setCustomClientIP($name, $custom_client_ip) : null;

                $this->flash->addMessage('notice', 'The client was created successfully.');
                return $response->withStatus(301)->withHeader('Location', '/ovpnphp/clients/' . $name);
            } else {
                $this->flash->addMessageNow('error', $result);

                return $this->view->render($response, 'clients/create.twig', [
                            'payload' => $payload,
                            'flashes' => $this->flash->getMessages(),
                ]);
            }
        } else {
            return $this->view->render($response, 'clients/create.twig', [
                        'payload' => $payload,
                        'errors' => $errors,
            ]);
        }
    }

    public function show(Request $request, Response $response, $args) {
        $name = $args['name'];
        $cert = $this->cert_repository->findByCommonName($name);

        return $this->view->render($response, 'clients/show.twig', [
                    'cert' => $cert,
                    'flashes' => $this->flash->getMessages(),
        ]);
    }

    public function delete(Request $request, Response $response, $args) {
        $name = $args['name'];

        $result = $this->ovpnapi->revokeClientCert($name);

        if (substr_count($result, 'certificate revoked')) {
            $this->flash->addMessage('notice', 'The client was revoked successfully.');
        } else {
            $this->flash->addMessage('error', $result);
        }

        return $response->withStatus(301)->withHeader('Location', '/ovpnphp/clients');
    }

    public function config(Request $request, Response $response, $args) {
        $name = $args['name'];

// @TODO validate $name

        $file = getenv('CLIENTS_CONFIG_DIR') . DIRECTORY_SEPARATOR . $name . '.tar.gz';

// @TODO generate config if not exist
// need to previus saved  IP info in database from create
        if (file_exists($file)) {
            $resource = fopen($file, 'rb');
            $stream = new \Slim\Http\Stream($resource);

            return $response->withHeader('Pragma', 'public')
                            ->withHeader('Expires', 0)
                            ->withHeader('Cache-Control', 'public, must-revalidate, post-check=0, pre-check=0')
                            ->withHeader('Content-Disposition', 'attachment; filename=' . basename($file))
                            ->withHeader('Content-Type', 'application/gzip')
                            ->withHeader('Content-Length', filesize($file))
                            ->withHeader('Connection', 'close')
                            ->withBody($stream);
        }
    }

    private function validateCreate($payload): array {
        /**
         * @TODO
         * 
         * validate IP with .env vars 'SERVER_NETMASK' and 'SERVER_NETWORK'
         * and http://php.net/manual/es/function.ip2long.php
         * 
         * View Respect custom rules:
         * 
         * v::with('Validation\\Rules\\');
         * 'ip' => v::ip()->notEmpty()->ipMask(SERVER_NETWORK, SERVER_NETMASK)->setName('IP'),
         */
        /* $network_l = ip2long(getenv('SERVER_NETWORK'));
          $netmask_l = ip2long(getenv('SERVER_NETMASK'));
          $ip_l = ip2long($payload['ip']);

          if (($netmask_l & $ip_l) == $network_l) {
          echo 'Valid IP Address.';
          } */

        $rules = [
            'name' => v::stringType()->notEmpty()/* ->regex('/^[a-zA-Z0-9-_]+$/') */->setName('Name'),
            'server_ip' => v::optional(v::ip()),
            'custom_client_ip' => v::optional(v::ip()),
        ];

        $data = [
            'name' => $payload['name'],
            'server_ip' => $payload['server_ip'],
            'custom_client_ip' => $payload['custom_client_ip'],
        ];

        $errors = [];
        foreach ($data as $key => $value) {
            try {
                $rules[$key]->check($value);
            } catch (\InvalidArgumentException $e) {
                $errors[$key] = $e->getMessage();
            }
        }

        /**
         * to return all errors
          try {
          $rules[$key]->assert($value);
          } catch (\Respect\Validation\Exceptions\NestedValidationException $ex) {
          print_r($ex->getMessages());
          }
         */
        return $errors;
    }

}
