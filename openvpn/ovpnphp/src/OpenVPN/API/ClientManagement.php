<?php

namespace OpenVPN\API;

/**
 * ClientManagement
 *
 * @author jrhrmsll
 */
class ClientManagement {

    private $scripts_path;

    /**
     * 
     * @param string $address
     * @param string $port
     */
    public function __construct(string $scripts_path) {
        $this->scripts_path = $scripts_path;
    }

    /**
     * 
     * @param string $client
     * @param string $server_ip
     */
    public function genClientConfig(string $client, string $server_ip) {
        $command = $this->scripts_path . "gen-client-config";
        shell_exec("sudo $command $client $server_ip");
    }

    /**
     * 
     * @param string $client
     * @param string $ip
     */
    public function setCustomClientIP(string $client, string $ip) {
        $command = $this->scripts_path . "custom-client-ip";
        shell_exec("sudo $command $client $ip");
    }

}
