<?php

namespace App\Services;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\Digest as AuthAdapter;
use \Zend\Authentication\Storage\NonPersistent as Storage;

/**
 * AuthService
 *
 * @author jrhrmsll
 */
class AuthService {

    private $auth;
    private $realm;
    private $filename;

    public function __construct($filename, $realm) {
        $this->filename = $filename;
        $this->realm = $realm;

        $this->auth = new AuthenticationService();
        $this->auth->setStorage(new Storage());
    }

    public function auth($username, $password) {
        $adapter = new AuthAdapter($this->filename, $this->realm, $username, $password);
        $result = $this->auth->authenticate($adapter);

        if ($result->isValid()) {
            return true;
        } else {
            return false;
        }
    }

}
