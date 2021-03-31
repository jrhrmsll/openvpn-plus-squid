<?php

namespace App\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**
 * SettingsController
 *
 * @author jrhrmsll
 */
class SettingsController {

    private $view;
    private $env_vars;

    public function __construct($view, $env_vars) {
        $this->view = $view;
        $this->env_vars = $env_vars;
    }

    public function index(Request $request, Response $response) {
        return $this->view->render($response, 'settings/index.twig', [
                    'settings' => $this->env_vars,
        ]);
    }

}
