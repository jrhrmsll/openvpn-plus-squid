<?php

namespace OpenVPN;

use OpenVPN\API\CertManagement;
use OpenVPN\API\ClientManagement;
use OpenVPN\API\ManagementInterface;

/**
 * API
 *
 * @author jrhrmsll
 */
class API {

    private $cert_mgmt;
    private $client_mgmt;
    private $mgmt_interface;

    /**
     * 
     * @param string $address
     * @param string $port
     * @param string $scripts_path
     */
    public function __construct(string $address, string $port, string $scripts_path) {
        $this->cert_mgmt = new CertManagement($scripts_path);
        $this->client_mgmt = new ClientManagement($scripts_path);
        $this->mgmt_interface = new ManagementInterface($address, $port);
    }

    /**
     * 
     * @return array
     */
    public function status(): array {
        return $this->mgmt_interface->status();
    }

    /**
     * 
     * @param string $common_name
     * @return string
     */
    public function genClientCert(string $common_name): string {
        $output = $this->cert_mgmt->genClientCert($common_name);

        return $output;
    }

    /**
     * 
     * @param string $client
     * @param string $server_ip
     */
    public function genClientConfig(string $client, string $server_ip) {
        $this->client_mgmt->genClientConfig($client, $server_ip);
    }

    /**
     * 
     * @param string $client
     * @param string $ip
     * @return type
     */
    public function setCustomClientIP(string $client, string $ip) {
        $output = $this->client_mgmt->setCustomClientIP($client, $ip);
        return $output;
    }

    /**
     * 
     * @param string $client
     * @return bool
     */
    public function isClientConnected(string $client): bool {

        $result = false;

        $entries = $this->status();
        foreach ($entries as $entry) {
            if (array_search($client, $entry) !== FALSE) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    /**
     * 
     * @param string $client
     * @return array
     */
    public function clientStatus(string $client): array {

        $info = array();

        $entries = $this->status();
        foreach ($entries as $entry) {
            if (array_search($client, $entry) !== FALSE) {
                $info = $entry;
                break;
            }
        }

        return $info;
    }

    /**
     * 
     * @return array
     */
    public function certList(): array {
        return $this->cert_mgmt->certList();
    }

    /**
     * 
     * @param string $common_name
     * @return array
     */
    public function certInfo(string $common_name): array {
        return $this->cert_mgmt->certInfo($common_name);
    }

    /**
     * 
     * @param string $common_name
     * @return string
     */
    public function revokeClientCert(string $common_name): string {
        $output = $this->cert_mgmt->revokeClientCert($common_name);
        $this->killClient($common_name);

        return $output;
    }

    /**
     * 
     * @param string $client
     * @return bool
     */
    public function killClient(string $client): bool {
        return $this->mgmt_interface->kill($client);
    }

    /**
     * 
     * @return string
     */
    public function version(): string {
        return $this->mgmt_interface->version();
    }

    /**
     * 
     * @return string
     */
    public function serverVirtualAddress(): string {
        list(,,, $ip) = explode(',', $this->mgmt_interface->state());
        return $ip;
    }

    /**
     * 
     * @return int
     */
    public function connections(): int {
        return $this->mgmt_interface->loadStats()['nclients'];
    }

}
