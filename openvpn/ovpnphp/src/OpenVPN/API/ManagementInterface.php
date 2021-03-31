<?php

namespace OpenVPN\API;

/**
 * ManagementInterface
 *
 * @author jrhrmsll
 */
class ManagementInterface {

    private $address;
    private $port;
    private $resource;

    /**
     * status entry fields names
     * 
     * "HEADER,CLIENT_LIST,Common Name,Real Address,Virtual Address,
     * Bytes Received,Bytes Sent,Connected Since,
     * Connected Since (time_t),Username"
     */
    private $fields = array(
        'Common Name',
        'Real Address',
        'Virtual Address',
        'Bytes Received',
        'Bytes Sent',
        'Connected Since',
        'Connected Since (time_t)',
        'Username',
    );

    /**
     * 
     * @param string $address
     * @param string $port
     */
    public function __construct(string $address, string $port) {
        $this->address = $address;
        $this->port = $port;
    }

    /**
     * 
     * @return type
     * @throws UnexpectedValueException
     */
    protected function connect() {
        $this->resource = stream_socket_client("tcp://$this->address:$this->port", $errno, $errorMessage, 5);

        if ($this->resource === false) {
            throw new UnexpectedValueException("Failed to connect: $errorMessage");
        }

        return $this->resource;
    }

    /**
     * 
     */
    protected function disconnect() {
        fclose($this->resource);
    }

    /**
     * 
     * @param string $line
     */
    protected function send(string $line) {
        fwrite($this->resource, "$line\r\n");
        usleep(100000);
    }

    /**
     * 
     * @return array
     */
    public function status(): array {
        $this->connect();

        $this->send("status 2");
        $this->send("quit");

        $clients = array();
        while (!feof($this->resource)) {
            $line = fgets($this->resource);

            if (preg_match("/^CLIENT_LIST/", $line)) {
                $parts = preg_split("/,/", $line);

                // remove "CLIENT_LIST" from top
                array_shift($parts);

                // combine arrays values for clarity
                $client = array_combine(array_values($this->fields), array_values(array_slice($parts, 0, count($this->fields))));

                array_push($clients, $client);
            }
        }

        $this->disconnect();

        return $clients;
    }

    /**
     * 
     * @param string $client
     * @return bool
     */
    public function kill(string $client): bool {
        $this->connect();

        $this->send("kill $client");
        $this->send("quit");

        $result = false;
        while (!feof($this->resource)) {
            $line = fgets($this->resource);

            if (preg_match("/^SUCCESS/", $line)) {
                $result = true;
                break;
            }
        }

        $this->disconnect();

        return $result;
    }

    /**
     * 
     * @return string
     */
    public function version(): string {
        $this->connect();

        $this->send("version");
        $this->send("quit");

        $result = '';
        while (!feof($this->resource)) {
            $line = fgets($this->resource);

            if (preg_match("/^OpenVPN Version:/", $line)) {
                $result .= $line;
                break;
            }
        }

        $this->disconnect();

        return $result;
    }

    /**
     * 
     * @return string
     */
    public function state(): string {
        $this->connect();

        $this->send("state");
        $this->send("quit");

        $result = '';
        while (!feof($this->resource)) {
            $line = fgets($this->resource);

            if (preg_match("/CONNECTED,SUCCESS,/", $line)) {
                $result .= $line;
                break;
            }
        }

        $this->disconnect();

        return $result;
    }

    /**
     * 
     * @return array
     */
    public function loadStats(): array {
        $this->connect();

        $this->send("load-stats");
        $this->send("quit");

        $load_stats = [
            'state' => 'SUCCESS',
        ];

        $result = '';
        while (!feof($this->resource)) {
            $line = fgets($this->resource);

            if (preg_match('/^SUCCESS: nclients=/', $line)) {
                $result .= $line;
                break;
            }
        }

        $this->disconnect();

        foreach (explode(',', str_replace('SUCCESS: ', '', $result)) as $item) {
            list($key, $value) = explode('=', $item);
            $load_stats[$key] = $value;
        }

        return $load_stats;
    }

}
