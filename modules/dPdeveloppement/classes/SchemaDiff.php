<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CMbException;

/**
 * Description
 */
class SchemaDiff
{
    /** @var DataAuditTargetSchema */
    private $first_schema;

    /** @var DataAuditTargetSchema */
    private $second_schema;

    /** @var array */
    private $schemas = [];

    /** @var array */
    private $diff = [];

    /** @var array */
    private $report = [];

    /**
     * SchemaDiff constructor.
     *
     * @param DataAuditTargetSchema $first_schema
     * @param DataAuditTargetSchema $second_schema
     *
     * @throws CMbException
     */
    public function __construct(DataAuditTargetSchema $first_schema, DataAuditTargetSchema $second_schema)
    {
        $this->first_schema  = $first_schema;
        $this->second_schema = $second_schema;

        $schemas = array_merge(
            $first_schema->getDatabaseNames(),
            $second_schema->getDatabaseNames()
        );

        $this->schemas = array_unique($schemas);

        $this->computeDiff();
    }

    /**
     * Get the merged host database list
     *
     * @return array
     */
    public function getSchemas(): array
    {
        return $this->schemas;
    }

    /**
     * Get the number of databases in error
     *
     * @return int
     */
    public function getSchemaErrorsCount(): int
    {
        return $this->report['schema']['count'];
    }

    /**
     * Get the number of tables in error
     *
     * @return int
     */
    public function getTableErrorsCount(): int
    {
        return $this->report['tables']['count'];
    }

    /**
     * Get the sigma of difference between databases
     *
     * @return array A db => sigma indexed array
     */
    public function getDbErrorsSigma(): array
    {
        return $this->report['tables']['sigma'];
    }

    /**
     * Tell if the database is in error
     *
     * @param string $database_name
     *
     * @return bool
     */
    public function isDbInError(string $database_name): bool
    {
        return isset($this->report['schema']['list'][$database_name]);
    }

    /**
     * Get the table list of a database
     *
     * @param string $database_name
     *
     * @return array
     */
    public function getTables(string $database_name): array
    {
        return array_keys($this->diff[$database_name]['tables']);
    }

    /**
     * Tell if the given table is missing on one of the two hosts
     *
     * @param string $database_name
     * @param string $table_name
     *
     * @return bool
     */
    public function isTableMissing(string $database_name, string $table_name): bool
    {
        return isset($this->report['tables']['existence'][$database_name][$table_name]);
    }

    /**
     * Tell if the given table has a difference (count) between the two hosts
     *
     * @param string $database_name
     * @param string $table_name
     *
     * @return bool
     */
    public function tableHasDifference(string $database_name, string $table_name): bool
    {
        return isset($this->report['tables']['diff'][$database_name][$table_name]);
    }

    /**
     * Tell if the given table exists on the first host
     *
     * @param string $database_name
     * @param string $table_name
     *
     * @return bool
     */
    public function doesTableExistForFirstHost(string $database_name, string $table_name): bool
    {
        return $this->diff[$database_name]['tables'][$table_name]['existence'][0];
    }

    /**
     * Tell if the given table exists on the second host
     *
     * @param string $database_name
     * @param string $table_name
     *
     * @return bool
     */
    public function doesTableExistForSecondHost(string $database_name, string $table_name): bool
    {
        return $this->diff[$database_name]['tables'][$table_name]['existence'][1];
    }

    /**
     * Get the number of rows of the given table on the first host
     *
     * @param string $database_name
     * @param string $table_name
     *
     * @return int
     */
    public function getTableCountForFirstHost(string $database_name, string $table_name): int
    {
        return $this->diff[$database_name]['tables'][$table_name]['count'][0];
    }

    /**
     * Get the number of rows of the given table on the second host
     *
     * @param string $database_name
     * @param string $table_name
     *
     * @return int
     */
    public function getTableCountForSecondHost(string $database_name, string $table_name): int
    {
        return $this->diff[$database_name]['tables'][$table_name]['count'][1];
    }

    /**
     * Compute the diff. between the two hosts
     *
     * @return void
     * @throws CMbException
     */
    private function computeDiff()
    {
        $diff = [];

        foreach ($this->schemas as $_db) {
            $_first_target_has_db  = $this->first_schema->doesDbExist($_db);
            $_second_target_has_db = $this->second_schema->doesDbExist($_db);

            $diff[$_db]['existence'][0] = $_first_target_has_db;
            $diff[$_db]['existence'][1] = $_second_target_has_db;

            $_db_tables = [];

            if ($_first_target_has_db && $_second_target_has_db) {
                $_db_tables = array_merge(
                    $this->first_schema->getTableNames($_db),
                    $this->second_schema->getTableNames($_db)
                );
            }

            $_db_tables = array_unique($_db_tables);

            foreach ($_db_tables as $_table) {
                $diff[$_db]['tables'][$_table] = [
                    'existence' => [],
                    'count'     => [],
                ];

                $_first_target_has_table  = $this->first_schema->doesTableExist($_db, $_table);
                $_second_target_has_table = $this->second_schema->doesTableExist($_db, $_table);

                $diff[$_db]['tables'][$_table]['existence'][0] = $_first_target_has_table;
                $diff[$_db]['tables'][$_table]['existence'][1] = $_second_target_has_table;

                if ($_first_target_has_table) {
                    $diff[$_db]['tables'][$_table]['count'][0] = $this->first_schema->countTableRows($_db, $_table);
                }

                if ($_second_target_has_table) {
                    $diff[$_db]['tables'][$_table]['count'][1] = $this->second_schema->countTableRows($_db, $_table);
                }
            }
        }

        $this->diff = $diff;

        $this->computeErrors();
    }

    /**
     * Compute the diff. errors between the two hosts
     *
     * @return void
     */
    private function computeErrors()
    {
        $report = [
            'schema' => [
                'list'  => [],
                'count' => 0,
            ],
            'tables' => [
                'existence' => [],
                'diff'      => [],
                'sigma'     => [],
                'count'     => 0,
            ],
        ];

        foreach ($this->diff as $_db => $_diffs) {
            // If sum of db existence booleans isn't equals to number of hosts (2), there is a missing db on one of the hosts
            if (array_sum($_diffs['existence']) !== count($_diffs['existence'])) {
                $report['schema']['list'][$_db] = [];
                $report['schema']['count']++;
            }

            foreach ($_diffs['tables'] as $_table => $_diff) {
                // If sum of table existence booleans isn't equals to number of hosts (2), there is a missing table on one of the hosts
                if (array_sum($_diff['existence']) !== count($_diff['existence'])) {
                    $report['tables']['existence'][$_db][$_table] = [];
                    $report['tables']['count']++;

                    continue;
                }

                // If the two host table count isn't the same (= we have more than one value after array_unique)
                if (count(array_unique($_diff['count'])) !== 1) {
                    // We put the absolute diff value in "sigma"
                    $_sigma = abs(reset($_diff['count']) - end($_diff['count']));

                    $report['tables']['diff'][$_db][$_table] = $_sigma;

                    if (!isset($report['tables']['sigma'][$_db])) {
                        $report['tables']['sigma'][$_db] = 0;
                    }

                    $report['tables']['sigma'][$_db] += $_sigma;

                    $report['tables']['count']++;
                }
            }
        }

        $this->report = $report;
    }
}
