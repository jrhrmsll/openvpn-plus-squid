<?php

namespace OpenVPN\DB;

/**
 * CertRepository
 *
 * @author jrhrmsll
 */
class CertRepository {

    private $pdo;

    /**
     * 
     * @param type $pdo
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * 
     */
    public function deleteAll() {
        $sql = 'DELETE FROM certs';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
    }

    /**
     * 
     * @param array $cert
     */
    public function save(array $cert) {
        $sql = 'INSERT INTO certs(serial, common_name, subject, cert_type, 
                status, start_date, expiry_date, revoked_at)
                VALUES(:serial, :common_name, :subject, :cert_type, :status,
                :start_date, :expiry_date, :revoked_at)';

        $stmt = $this->pdo->prepare($sql);

        $cert['Start Date'] = $this->formatDateString('M j H:i:s Y e', \DateTime::ISO8601, $cert['Start Date']);
        $cert['Expiry Date'] = $this->formatDateString('M j H:i:s Y e', \DateTime::ISO8601, $cert['Expiry Date']);
        $cert['Revocation Date'] = $this->formatDateString('ymdHise', \DateTime::ISO8601, $cert['Revocation Date']);

        $stmt->bindValue(':serial', $cert['Serial Number']);
        $stmt->bindValue(':common_name', $cert['Common Name']);
        $stmt->bindValue(':subject', $cert['Subject']);
        $stmt->bindValue(':cert_type', $cert['Cert Type']);
        $stmt->bindValue(':status', $cert['Status']);
        $stmt->bindValue(':start_date', $cert['Start Date']);
        $stmt->bindValue(':expiry_date', $cert['Expiry Date']);
        $stmt->bindValue(':revoked_at', $cert['Revocation Date']);

        $stmt->execute();
    }

    /**
     * 
     * @param array $cert
     */
    public function update(array $cert) {
        $sql = 'UPDATE certs SET
                    status = :status,
                    revoked_at = :revoked_at
                WHERE
                    common_name = :common_name';

        $stmt = $this->pdo->prepare($sql);

        $cert['Revocation Date'] = $this->formatDateString('ymdHise', \DateTime::ISO8601, $cert['Revocation Date']);

        $stmt->bindValue(':common_name', $cert['Common Name']);
        $stmt->bindValue(':status', $cert['Status']);
        $stmt->bindValue(':revoked_at', $cert['Revocation Date']);

        $stmt->execute();
    }

    /**
     * 
     * @return array
     */
    public function findClients(): array {
        $stmt = $this->pdo->query('SELECT * FROM certs WHERE cert_type="Client" ORDER BY start_date ASC');

        $timezone = $_SESSION['timezone'];
        
        $clients = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $clients[] = [
                'Serial Number' => $row['serial'],
                'Common Name' => $row['common_name'],
                'Subject' => $row['subject'],
                'Cert Type' => $row['cert_type'],
                'Status' => $row['status'],
                'Start Date' => $this->formatDateString(\DateTime::ISO8601, 'D, d M Y H:i:s', $row['start_date'], $timezone),
                'Expiry Date' => $this->formatDateString(\DateTime::ISO8601, 'D, d M Y H:i:s', $row['expiry_date'], $timezone),
                'Revocation Date' => $this->formatDateString(\DateTime::ISO8601, 'D, d M Y H:i:s', $row['revoked_at'], $timezone),
            ];
        }

        return $clients;
    }

    /**
     * 
     * @param string $common_name
     * @return array
     */
    public function findByCommonName(string $common_name): array {
        $stmt = $this->pdo->prepare('SELECT * FROM certs WHERE common_name = :common_name LIMIT 1');

        $stmt->execute([
            ':common_name' => $common_name,
        ]);

        $timezone = $_SESSION['timezone'];
        
        $cert = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $cert = [
                'Serial Number' => $row['serial'],
                'Common Name' => $row['common_name'],
                'Subject' => $row['subject'],
                'Cert Type' => $row['cert_type'],
                'Status' => $row['status'],
                'Start Date' => $this->formatDateString(\DateTime::ISO8601, 'D, d M Y H:i:s', $row['start_date'], $timezone),
                'Expiry Date' => $this->formatDateString(\DateTime::ISO8601, 'D, d M Y H:i:s', $row['expiry_date'], $timezone),
                'Revocation Date' => $this->formatDateString(\DateTime::ISO8601, 'D, d M Y H:i:s', $row['revoked_at'], $timezone),
            ];
        }

        return $cert;
    }

    public function findExpiredCerts(): array {
        //$stmt = $this->pdo->query('SELECT common_name FROM certs WHERE expiry_date <= datetime("now") AND status == "V"');
        $stmt = $this->pdo->query('SELECT common_name FROM certs WHERE expiry_date <= datetime(\'now\') AND status == \'V\'');

        $certs = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $certs[] = $row['common_name'];
        }

        return $certs;
    }

    /**
     * 
     * @return array
     */
    public function clientCertsStats(): array {
        $stmt = $this->pdo->query('SELECT status, COUNT(status) as "count" FROM
        certs WHERE cert_type = "Client" GROUP BY status');

        $stats = ['Valid' => 0, 'Expired' => 0, 'Revoked' => 0, 'Total' => 0];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            switch ($row['status']) {
                case 'V':
                    $stats['Valid'] = $row['count'];
                    break;
                case 'E':
                    $stats['Expired'] = $row['count'];
                    break;
                case 'R':
                    $stats['Revoked'] = $row['count'];
                    break;
                default :
                    continue;
            }
        }

        $stats['Total'] = $stats['Valid'] + $stats['Expired'] + $stats['Revoked'];

        return $stats;
    }

    /**
     * 
     * @param string $old_format
     * @param string $new_format
     * @param string $date_string
     * @param string $timezone
     * @return string
     */
    private function formatDateString(string $old_format, string $new_format, string $date_string, string $timezone = null): string {
        if ($date_string !== '') {
            $date = \DateTime::createFromFormat($old_format, $date_string);

            if ($timezone) {
                $tz = new \DateTimeZone($timezone);
                $date->setTimezone($tz);
            }

            return $date->format($new_format);
        }

        return $date_string;
    }

}
