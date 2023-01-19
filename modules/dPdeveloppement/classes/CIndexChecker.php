<?php

/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Developpement;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\FieldSpecs\CDateSpec;
use Ox\Core\FieldSpecs\CDateTimeSpec;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Core\FieldSpecs\CTimeSpec;
use Ox\Core\Module\CModule;
use Throwable;

/**
 * Description
 */
class CIndexChecker
{
    public const ERROR_ALL           = 'all';
    public const ERROR_MISSING_DB    = 'missing_db';
    public const ERROR_UNEXPECTED_DB = 'unexpected_db';
    public const ERROR_TYPES         = [self::ERROR_ALL, self::ERROR_MISSING_DB, self::ERROR_UNEXPECTED_DB];

    public const KEY_INDEX  = 'index';
    public const KEY_UNIQUE = 'unique';
    public const KEY_TYPES  = [self::KEY_INDEX, self::KEY_UNIQUE];

    /** @var CSQLDataSource */
    private $ds;

    /** @var string */
    private $module;

    /** @var array */
    private $classes_to_check = [];

    /** @var array */
    private $errors = [];

    /** @var string */
    private $error_type;

    /** @var string */
    private $key_type;

    /** @var bool */
    private $show_all_fields = false;

    /** @var int */
    private $count_missing_db = 0;

    /** @var int */
    private $count_not_expected_db = 0;

    /** @var string[] */
    private $index_types = [CRefSpec::class, CDateTimeSpec::class, CDateSpec::class, CTimeSpec::class];

    public function __construct(
        string $key_type,
        string $error_type = self::ERROR_ALL,
        string $module = null,
        bool $show_all_field = false
    ) {
        $this->error_type = (in_array($error_type, self::ERROR_TYPES)) ? $error_type : self::ERROR_ALL;
        $this->key_type   = (in_array($key_type, self::KEY_TYPES)) ? $key_type : self::KEY_INDEX;

        // Need to keep module in string for the getClassChildren to work
        $this->module                = $module;
        $this->count_missing_db      = 0;
        $this->count_not_expected_db = 0;
        $this->show_all_fields       = $show_all_field;
    }

    /**
     * Check indexes existing in DB and that should exists
     *
     * @return array
     * @throws Exception
     */
    public function check(): array
    {
        $this->init();

        foreach ($this->classes_to_check as $_class_name) {
            /** @var CStoredObject $instance */
            if ($instance = $this->getInstance($_class_name)) {
                $specs = $instance->getSpec();

                if (!$specs->table) {
                    continue;
                }

                $existing_indexes = $this->getExistingIndexes($specs->table);
                $expected_indexes = ($this->key_type === self::KEY_INDEX)
                    ? $this->getExpectedIndexes($instance)
                    : $this->getExpectedUniques($instance);

                if ($this->error_type === self::ERROR_ALL || $this->error_type === self::ERROR_MISSING_DB) {
                    $this->checkExpectedIndexes($_class_name, $expected_indexes, $existing_indexes);
                }

                if ($this->error_type === self::ERROR_ALL || $this->error_type === self::ERROR_UNEXPECTED_DB) {
                    $this->checkNonExpectedIndexes(
                        $_class_name,
                        $expected_indexes,
                        $existing_indexes,
                        ($this->key_type === self::KEY_INDEX) ? $this->getExpectedUniques($instance) : []
                    );
                }
            }
        }

        return $this->errors;
    }

    private function checkNonExpectedIndexes(
        string $class_name,
        array $expected,
        array $existing,
        array $expect_uniques
    ): void {
        foreach ($existing as $_existing) {
            // Multi index if more than one field
            if (count($_existing) > 1) {
                $field_name = implode('|', $_existing);
                $index_ok   = $this->handleMultiDbIndex($_existing, $expected);
                if (!$index_ok) {
                    // Check if index has been declared as unique
                    $index_ok = $this->handleMultiDbIndex($_existing, $expect_uniques);
                }
            } else {
                $field_name = $_existing[1];
                $index_ok   = $this->handleSingleDbIndex($field_name, $expected);
                if (!$index_ok) {
                    // Check if index has been declared as unique
                    $index_ok = $this->handleSingleDbIndex($field_name, $expect_uniques);
                }
            }

            if (!$index_ok) {
                $this->errors[$class_name][$field_name] = 'not_expected_db';
                $this->count_not_expected_db++;
            } elseif ($this->show_all_fields) {
                $this->errors[$class_name][$field_name] = 'ok';
            }
        }
    }

    private function handleSingleDbIndex(string $field_name, array $expected): bool
    {
        foreach ($expected as $_expected) {
            if (!is_array($_expected) && $field_name == $_expected) {
                // Index is okay if it is expected by the props
                return true;
            }
        }

        return false;
    }

    private function handleMultiDbIndex(array $existings, array $expected): bool
    {
        foreach ($expected as $_expected) {
            // Do not search if expected index have more items than the existing one
            if (!is_array($_expected) || count($_expected) != count($existings)) {
                continue;
            }

            $all_ok = true;
            // Do not use array_diff, fields have to be in the same order
            foreach ($existings as $_pos => $_field) {
                if ($_expected[$_pos] != $_field) {
                    $all_ok = false;
                    break;
                }
            }

            if ($all_ok) {
                return true;
            }
        }

        return false;
    }

    private function checkExpectedIndexes(string $class_name, array $expected, array $existing): void
    {
        foreach ($expected as $_expected) {
            if (is_array($_expected)) {
                $field_name = implode('|', $_expected);
                $index_ok   = $this->handleMultiFieldIndex($_expected, $existing);
            } else {
                $field_name = $_expected;
                $index_ok   = $this->handleSingleFieldIndex($_expected, $existing);
            }

            if (!$index_ok) {
                $this->errors[$class_name][$field_name] = 'missing_db';
                $this->count_missing_db++;
            } elseif ($this->show_all_fields) {
                $this->errors[$class_name][$field_name] = 'ok';
            }
        }
    }

    private function handleSingleFieldIndex(string $expected, array $existing): bool
    {
        foreach ($existing as $_fields) {
            // We can use a multi index if it start with the column
            /*if (count($_fields) > 1) {
              continue;
            }*/

            if ($_fields[1] == $expected) {
                return true;
            }
        }

        return false;
    }

    private function handleMultiFieldIndex(array $expected, array $existing): bool
    {
        foreach ($existing as $_fields) {
            // Multi index expected
            // There can be a longer index usable
            if (count($_fields) < count($expected)) {
                continue;
            }

            if ($expected[1] == $_fields[1] && $expected[2] == $_fields[2]) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws Exception
     */
    private function getExistingIndexes(string $table_name): array
    {
        if (!$this->ds->hasTable($table_name)) {
            return [];
        }

        $result = $this->getExistingIndexesFromDb($table_name);

        $indexes = [];
        foreach ($result as $_result) {
            if (!isset($indexes[$_result['Key_name']])) {
                $indexes[$_result['Key_name']] = [
                    $_result['Seq_in_index'] => $_result['Column_name'],
                ];
            } else {
                $indexes[$_result['Key_name']][$_result['Seq_in_index']] = $_result['Column_name'];
            }
        }

        return $indexes;
    }

    private function getInstance(string $class_name): ?CStoredObject
    {
        try {
            return new $class_name();
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * @throws Exception
     */
    private function init(): void
    {
        $this->ds = CSQLDataSource::get('std');

        $class_map              = CClassMap::getInstance();

        if ($this->module) {
            $this->classes_to_check = $class_map->getClassChildren(CStoredObject::class, false, false, $this->module);
        } else {
            $modules = CModule::getInstalled();
            foreach ($modules as $_mod) {
                $this->classes_to_check = array_merge(
                    $this->classes_to_check,
                    $class_map->getClassChildren(CStoredObject::class, false, false, $_mod->mod_name)
                );
            }
        }
    }

    /**
     * @throws Exception
     */
    private function getExistingIndexesFromDb(string $table_name): array
    {
        $query = "SHOW INDEXES FROM {$table_name} WHERE Key_name != 'PRIMARY'";

        if ($this->key_type === self::KEY_UNIQUE) {
            $query .= ' AND Non_unique = 0';
        } /*else {
            $query .= ' AND Non_unique = 1';
        }*/

        return $this->ds->loadList($query);
    }

    /**
     * @param CStoredObject $object
     *
     * @return array
     * @throws Exception
     */
    private function getExpectedIndexes(CStoredObject $object): array
    {
        $expected_indexes = [];
        $specs            = $object->getSpecs();
        $primary_key      = $object->getSpec()->key;
        foreach ($specs as $_field_name => $_spec) {
            if (strpos($_field_name, '_') === 0 || $_field_name === $primary_key) {
                continue;
            }

            // Index have to be on a field of expected type
            // Field doesn't have to have the spec index|0
            if ((in_array(get_class($_spec), $this->index_types) && ($_spec->index !== "0")) || $_spec->index) {
                // Date, datetime and time specs does not have a meta field and cannot be a multi index
                if (property_exists($_spec, 'meta') && $_spec->meta) {
                    $expected_indexes[] = [
                        1 => $_spec->meta,
                        2 => $_field_name,
                    ];
                } elseif ($_spec->index) {
                    // If $_spec->index but index !== 1 we may want to force a multi index
                    $index_parts = explode('|', $_spec->index);

                    // Forced single index
                    if ($index_parts[0] === '1') {
                        $expected_indexes[] = $_field_name;
                    } else {
                        // Forced multi index
                        $multi_index = [1 => $_field_name];

                        $i = 0;
                        foreach ($index_parts as $_part) {
                            if (isset($specs[$_part])) {
                                $multi_index[$i + 2] = $_part;
                            }
                            $i++;
                        }

                        $expected_indexes[] = $multi_index;
                    }
                } else {
                    // Default single index
                    $expected_indexes[] = $_field_name;
                }
            }
        }

        return $expected_indexes;
    }

    public function getExpectedUniques(CStoredObject $object): array
    {
        $expected_indexes = [];
        $uniques          = $object->getSpec()->uniques;

        // Add declared iodkus indexes
        if ($iodkus = $object->getSpec()->iodkus) {
            $uniques = array_merge($uniques, $iodkus);
        }

        foreach ($uniques as $_fields) {
            if (count($_fields) > 1) {
                $i     = 1;
                $multi = [];
                foreach ($_fields as $_field) {
                    $multi[$i] = $_field;
                    $i++;
                }
                $expected_indexes[] = $multi;
            } else {
                $expected_indexes[] = $_fields[0];
            }
        }

        return $expected_indexes;
    }

    /**
     * @return int
     */
    public function getCountMissingDb(): int
    {
        return $this->count_missing_db;
    }

    /**
     * @return int
     */
    public function getCountNotExpectedDb(): int
    {
        return $this->count_not_expected_db;
    }
}
