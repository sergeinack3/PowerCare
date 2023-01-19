<?php

/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\System\Forms\CExClass;
use Ox\Mediboard\System\Forms\CExObject;
use Throwable;

/**
 * Description
 */
class CTablesIntegrityChecker
{
    public const TABLE_MISSING = 'table_missing';
    public const CLASS_MISSING = 'class_missing';

    /** @var array */
    private $db_tables = [];

    /** @var array */
    private $tables_ok = [];

    /** @var ?string */
    private $module = null;

    /** @var array */
    private $classes_to_test = [];

    /** @var CTableIntegrity */
    private $tables_integrity = [];

    /** @var array */
    private $ds = [];

    /** @var string */
    private $dsn;

    /** @var string */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function setDsn(string $dsn): void
    {
        $this->dsn = $dsn;
    }

    public function setModule(string $module): void
    {
        $this->module = $module;
    }

    public function checkTablesIntegrity(): array
    {
        /** @var CStoredObject[] classes_to_test */
        $this->classes_to_test = $this->getClassesToTest();

        /** @var CStoredObject $_instance */
        foreach ($this->classes_to_test as $_instance) {
            if (!$_instance->isModelObjectAbstract() && $_instance->_ref_module) {
                [$dsn, $table_name] = $this->getTableFromInstance($_instance);

                if (!$dsn) {
                    continue;
                }

                if ($this->dsn && $dsn !== $this->dsn) {
                    continue;
                }

                $this->tables_integrity[] = new CTableIntegrity(
                    get_class($_instance),
                    $table_name,
                    $_instance->_ref_module,
                    $dsn
                );

                // Init DS
                if (!isset($this->ds[$dsn])) {
                    $ds = CSQLDataSource::get($dsn, true);
                    $this->ds[$dsn] = ($ds) ? CAppUI::conf("db {$dsn} dbname") : false;
                }
            }
        }

        if ((!$this->dsn || $this->dsn === 'std') && (!$this->module || !$this->module === 'system')) {
            $this->tables_integrity = $this->addExObjectTables($this->tables_integrity);
        }

        $this->db_tables = $this->getTablesFromDb(array_keys($this->ds));
        $this->db_tables = $this->countRowForDs($this->ds);

        /** @var CTableIntegrity $_integrity */
        foreach ($this->tables_integrity as $_key => &$_integrity) {
            if (isset($this->db_tables[$_integrity->getTableName()])) {
                $_integrity->setRowCount($this->db_tables[$_integrity->getTableName()]);
                $_integrity->setTableExists(true);

                $this->tables_ok[$_integrity->getTableName()] = $this->db_tables[$_integrity->getTableName()];

                unset($this->db_tables[$_integrity->getTableName()]);

                if ($this->type === self::TABLE_MISSING) {
                    unset($this->tables_integrity[$_key]);
                }
            } elseif (isset($this->tables_ok[$_integrity->getTableName()])) {
                $_integrity->setRowCount($this->tables_ok[$_integrity->getTableName()]);
                $_integrity->setTableExists(true);

                if ($this->type === self::TABLE_MISSING) {
                    unset($this->tables_integrity[$_key]);
                }
            }
        }

        if (!$this->module && $this->type !== self::TABLE_MISSING) {
            if ($this->db_tables && $this->type === self::CLASS_MISSING) {
                $this->tables_integrity = [];
            }
            foreach ($this->db_tables as $_table_name => $_count) {
                $table_integrity = new CTableIntegrity(null, $_table_name);
                $table_integrity->setRowCount(($_count !== null) ? $_count : 0);
                $this->tables_integrity[] = $table_integrity;
            }
        }

        return $this->tables_integrity;
    }

    private function getClassesToTest(): array
    {
        $class_map = CClassMap::getInstance();

        return $class_map->getClassChildren(CStoredObject::class, true, true, $this->module);
    }

    private function getTableFromInstance(CStoredObject $instance): array
    {
        if ($instance instanceof CExObject) {
            return [null, null];
        }

        $spec = $instance->getSpec();

        return [
            $spec->dsn,
            $spec->table,
        ];
    }

    private function getTablesFrominstances(array $instances): array
    {
        $tables = [];
        /** @var CStoredObject $_instance */
        foreach ($instances as $_instance) {
            if ($_instance instanceof CExObject) {
                continue;
            }

            $spec = $_instance->getSpec();
            if (!isset($tables[$spec->dsn])) {
                $tables[$spec->dsn] = [];
            }

            $tables[$spec->dsn][get_class($_instance)] = $spec->table;
        }

        // Handle ex_object special table names
        $tables['std'] = array_merge($tables['std'], $this->addExobjectTables());

        return $tables;
    }

    private function getTablesFromDb(array $dsn): array
    {
        $tables = [];
        // TODO Filter by DSN
        foreach ($dsn as $_dsn) {
            $tables = array_merge($tables, $this->getTablesForDsn($_dsn));
        }

        return $tables;
    }

    private function getTablesForDsn(string $dsn): array
    {
        $ds = CSQLDataSource::get($dsn, true);
        if (!$ds) {
            return [];
        }

        $query = "SHOW TABLES";

        return $ds->loadColumn($query);
    }

    private function addExObjectTables(array $class_integrity = []): array
    {
        $ex_object_tables = $this->getExObjectTables();

        foreach ($ex_object_tables as $_ex_object_name => $_table_name) {
            $class_integrity[] = new CTableIntegrity($_ex_object_name, $_table_name, 'system', 'std');
        }

        return $class_integrity;
    }

    private function getExObjectTables(): array
    {
        $ex_class = new CExClass();
        $ids = $ex_class->loadIds();

        $ex_object_tables = [];
        foreach ($ids as $_id) {
            $ex_object_tables["CExObject-{$_id}"] = "ex_object_{$_id}";
        }

        return $ex_object_tables;
    }

    private function countRowForDs(array $db_names): ?array
    {
        $ds = CSQLDataSource::get('std');

        $query = new CRequest();
        $query->addSelect(['TABLE_NAME', 'TABLE_ROWS']);
        $query->addTable('INFORMATION_SCHEMA.`TABLES`');
        $query->addWhere(
            [
                'TABLE_SCHEMA' => $ds->prepareIn($db_names),
            ]
        );

        return $ds->loadHashList($query->makeSelect());
    }
}
