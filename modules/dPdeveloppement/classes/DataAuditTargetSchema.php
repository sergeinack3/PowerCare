<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CMbException;
use Ox\Core\CMbString;
use PDO;

/**
 * Description
 */
class DataAuditTargetSchema
{
    /** @var array Schema */
    private $schema = [];

    /**
     * @param PDO $schema_connection
     *
     * @return void
     */
    public function parse(PDO $schema_connection)
    {
        $stmt = $schema_connection->prepare(
            'SELECT `TABLE_SCHEMA`, `TABLE_NAME`, `TABLE_ROWS`
       FROM `TABLES`
       WHERE `TABLE_TYPE` = "BASE TABLE";'
        );

        $stmt->execute();
        $result = $stmt->fetchAll();

        $parsed = [];
        foreach ($result as $_result) {
            $_dbname = CMbString::lower($_result['TABLE_SCHEMA']);
            $_table_name = CMbString::lower($_result['TABLE_NAME']);

            if (!isset($parsed[$_dbname])) {
                $parsed[$_dbname] = [];
            }

            $parsed[$_dbname][$_table_name] = $_result['TABLE_ROWS'];
        }

        $this->schema = $parsed;
    }

    /**
     * @param string $database_name
     *
     * @return bool
     */
    public function doesDbExist(string $database_name): bool
    {
        return isset($this->schema[$database_name]);
    }

    /**
     * @param string $database_name
     * @param string $table_name
     *
     * @return bool
     */
    public function doesTableExist(string $database_name, string $table_name): bool
    {
        if (!$this->doesDbExist($database_name)) {
            return false;
        }

        return isset($this->schema[$database_name][$table_name]);
    }

    /**
     * @return array
     */
    public function getDatabaseNames(): array
    {
        return array_keys($this->schema);
    }

    /**
     * @param string $database_name
     *
     * @return array
     * @throws CMbException
     */
    public function getTableNames(string $database_name): array
    {
        if (!$this->doesDbExist($database_name)) {
            throw new CMbException("DataAuditTargetSchema-error-Database '%s' does not exist", $database_name);
        }

        return array_keys($this->schema[$database_name]);
    }

    /**
     * @param string $database_name
     * @param string $table_name
     *
     * @return int
     * @throws CMbException
     */
    public function countTableRows(string $database_name, string $table_name): int
    {
        if (!$this->doesTableExist($database_name, $table_name)) {
            throw new CMbException(
                "DataAuditTargetSchema-error-Table '%s.%s' does not exist",
                $database_name,
                $table_name
            );
        }

        return $this->schema[$database_name][$table_name];
    }
}
