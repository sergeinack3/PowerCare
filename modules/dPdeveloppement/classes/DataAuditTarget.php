<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use PDO;

/**
 * Description
 */
class DataAuditTarget
{
    /** @var string */
    private const DS_REGEXP = '/^\<(?P<ds>\w+)\>$/';

    /** @var string */
    private const PDO_REGEXP = '/^(?P<user>\w+)(:?(?P<pass>\w+)?)@(?P<host>([a-zA-Z0-9_\.]+))\/(?P<db>\w+)(:?(?P<port>\d+)?)$/';

    /** @var string Hostname */
    private $hostname;

    /** @var PDO DB connection */
    private $connection;

    /** @var PDO DB Schema connection */
    private $schema_connection;

    /**
     * DataAuditTarget constructor.
     *
     * @param string $hostname
     *
     * @throws CMbException
     */
    public function __construct(string $hostname)
    {
        if (!$hostname) {
            throw new CMbException('common-error-Missing parameter');
        }

        $this->parseHostname($hostname);
    }

    private function parseHostname(string $hostname): void
    {
        $db_name = null;
        $db_host = null;
        $db_port = 3306;
        $db_user = null;
        $db_pass = null;

        $matches = [];
        if (preg_match(self::DS_REGEXP, $hostname, $matches)) {
            $ds = CAppUI::conf("db {$matches['ds']}");

            $db_host = $ds['dbhost'];
            $db_name = $ds['dbname'];
            $db_user = $ds['dbuser'];
            $db_pass = $ds['dbpass'];
        } elseif (preg_match(self::PDO_REGEXP, $hostname, $matches)) {
            $db_host = $matches['host'];
            $db_name = $matches['db'];
            $db_user = $matches['user'];
            $db_pass = ($matches['pass']) ?? null;
            $db_port = ($matches['port']) ?? $db_port;
        }

        if (!$db_name || !$db_host) {
            throw new CMbException('common-error-Missing parameter');
        }

        $this->hostname          = "{$db_name}@{$db_host}";
        $this->connection        = $this->initConnection($db_host, $db_name, $db_port, $db_user, $db_pass);
        $this->schema_connection = $this->initSchemaConnection($db_host, $db_port, $db_user, $db_pass);
    }

    /**
     * Initialize the DB connection
     *
     * @param string      $hostname
     * @param string      $db_name
     * @param int         $port
     * @param string|null $db_user
     * @param string|null $ds_pass
     *
     * @return PDO
     */
    private function initConnection(
        string $hostname,
        string $db_name,
        int $port,
        ?string $db_user = null,
        ?string $ds_pass = null
    ): PDO {
        return new PDO(
            "mysql:dbname={$db_name};host={$hostname};port={$port}",
            $db_user,
            $ds_pass
        );
    }

    /**
     * Initialize the DB Schema connection
     *
     * @param string      $hostname
     * @param int         $port
     * @param string|null $db_user
     * @param string|null $ds_pass
     *
     * @return PDO
     */
    private function initSchemaConnection(
        string $hostname,
        int $port,
        ?string $db_user = null,
        ?string $ds_pass = null
    ): PDO {
        return $this->initConnection($hostname, 'information_schema', $port, $db_user, $ds_pass);
    }

    /**
     * Get the target hostname
     *
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * Get schema
     *
     * @return DataAuditTargetSchema
     */
    public function parseSchema(): DataAuditTargetSchema
    {
        $schema = new DataAuditTargetSchema();
        $schema->parse($this->schema_connection);

        return $schema;
    }

    /**
     * Get logs
     *
     * @param string $start_date
     * @param string $end_date
     *
     * @return DataAuditTargetLogs
     * @throws CMbException
     */
    public function parseLogs(string $start_date, string $end_date): DataAuditTargetLogs
    {
        $logs = new DataAuditTargetLogs();
        $logs->parse($this->connection, $start_date, $end_date);

        return $logs;
    }
}
