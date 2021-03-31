<?php

namespace OpenVPN\API;

/**
 * CertManagement
 *
 * @author jrhrmsll
 */
class CertManagement {

    private $scripts_path;

    public function __construct(string $scripts_path) {
        $this->scripts_path = $scripts_path;
    }

    /**
     * 
     * @param string $common_name
     * @return string
     */
    public function genClientCert(string $common_name): string {
        $command = $this->scripts_path . "gen-client-cert";
        $output = shell_exec("sudo $command $common_name 2>&1");

        return $output;
    }

    /**
     * 
     * @param string $common_name
     * @return string
     */
    public function revokeClientCert(string $common_name): string {
        $command = $this->scripts_path . "revoke-client-cert";
        $output = shell_exec("sudo $command $common_name 2>&1");

        return $output;
    }

    /**
     * 
     * @param string $common_name
     * @return array
     */
    public function certInfo(string $common_name): array {
        $command = $this->scripts_path . "cert-info";
        $output = shell_exec("sudo $command $common_name");

        $info = [
            'Serial Number' => '',
            'Common Name' => '',
            'Subject' => '',
            'Cert Type' => '',
            'Status' => '',
            'Start Date' => '',
            'Expiry Date' => '',
            'Revocation Date' => '',
        ];

        $lines = explode(PHP_EOL, $output);

        $info['Common Name'] = $common_name;

        foreach ($lines as $line) {
            if (preg_match('/^Cert Type=/', $line)) {
                list(, $info['Cert Type']) = preg_split('/^Cert Type=/', $line);
            }

            if (preg_match('/^Index Entry=/', $line)) {
                list(, $index_entry) = preg_split('/^Index Entry=/', $line);

                if (preg_match('/^(V|E)/', $index_entry)) {
                    // pass limit to preg_split to avoid split subject with spaces
                    list( $info['Status'],, $info['Serial Number'],, $info['Subject']) = preg_split('/ /', $index_entry, 5);
                }

                if (preg_match('/^R/', $index_entry)) {
                    // pass limit to preg_split to avoid split subject with spaces
                    list( $info['Status'],, $info['Revocation Date'], $info['Serial Number'],, $info['Subject']) = preg_split('/ /', $index_entry, 6);
                }
            }

            if (preg_match('/^notBefore=/', $line)) {
                list(, $info['Start Date']) = preg_split('/^notBefore=/', $line);
            }

            if (preg_match('/^notAfter=/', $line)) {
                list(, $info['Expiry Date']) = preg_split('/^notAfter=/', $line);
            }
        }

        return $info;
    }

    /**
     * 
     * @return array
     */
    public function certList(): array {
        $command = $this->scripts_path . "cert-list";
        $output = shell_exec("sudo $command");

        $certs = explode(PHP_EOL, $output);
        array_pop($certs);

        return $certs;
    }

}
