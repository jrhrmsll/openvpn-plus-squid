<?php

namespace OpenVPN\DB;

use \PDO;

/**
 * SQLiteConnector
 *
 * @author jrhrmsll
 */
class SQLiteConnector {

    private $path;
    private $pdo;

    public function __construct($path) {
        $this->path = $path;
    }

    public function connect() {
        try {
            if ($this->pdo === null) {
                $this->pdo = new \PDO("sqlite:" . $this->path);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }

            return $this->pdo;
        } catch (\PDOException $e) {
            echo $e->getMessage();
        }
    }

}
