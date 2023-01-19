<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Composer\Semver\Comparator as SemverComparator;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Elastic\ElasticObject;
use Ox\Core\Module\CModule;
use Ox\Core\Security\Crypt\Alg;
use Ox\Core\Security\Crypt\Mode;
use Ox\Mediboard\System\CPreferences;
use Ox\Mediboard\System\Keys\Exceptions\CouldNotPersistKey;
use Ox\Mediboard\System\Keys\Key;
use Ox\Mediboard\System\Keys\KeyBuilder;
use stdClass;

/**
 * Setup abstract class
 * Install, upgrade or remove modules
 */
class CSetup implements IShortNameAutoloadable
{
    // Public vars
    public $mod_name;
    public $mod_version;
    public $mod_type = "user";
    public $mod_requires_php;

    /** @var CSQLDataSource */
    public $ds;

    // Protected vars
    public  $messages             = [];
    public  $revisions            = [];
    public  $queries              = [];
    public  $preferences          = [];
    public  $functions            = [];
    public  $dependencies         = [];
    public  $tables               = [];
    public  $datasources          = [];
    private $elastic_dependencies = [];
    public  $config_moves         = [];
    private $registered_keys      = [];

    static private $_old_pref_system            = null;
    static private $_old_pref_system_restricted = null;

    public static $current = null;

    /**
     * Setup constructor, initializes the datasource
     */
    function __construct()
    {
        $this->ds = CSQLDataSource::get("std");
    }

    /**
     * Create a revision of a given name
     *
     * @param string $revision Revision number of form x.y
     *
     * @return void
     */
    function makeRevision($revision)
    {
        if (in_array($revision, $this->revisions)) {
            CModelObject::error("Revision-revision%s-already-exists", $revision);
        }

        $this->revisions[]                = $revision;
        $this->queries     [$revision]    = [];
        $this->preferences [$revision]    = [];
        $this->functions   [$revision]    = [];
        $this->dependencies[$revision]    = [];
        $this->config_moves[$revision]    = [];
        $this->registered_keys[$revision] = [];
        end($this->revisions);
    }

    /**
     * Add a message for a specified revision
     *
     * @param string $message the message
     *
     * @return array ([$version] => [$message])
     */
    function addUpdateMessage($message)
    {
        return $this->messages[end($this->revisions)] = $message;
    }

    /**
     * Create an empty revision
     *
     * @param string $revision Revision number of form x.y
     *
     * @return void
     */
    function makeEmptyRevision($revision)
    {
        $this->makeRevision($revision);
        $this->addQuery("SELECT 0");
    }

    /**
     * Add a callback method to be executed
     * The method must return true/false
     *
     * @param callable $method_name The methode to execute (from $this)
     *
     * @return void
     */
    function addMethod($method_name)
    {
        if (!is_string($method_name)) {
            trigger_error("You must give a method name", E_USER_WARNING);

            return;
        }

        $callable = [$this, $method_name];
        if (!is_callable($callable)) {
            $method = get_class($this) . '->' . $method_name;
            trigger_error("'$method' is not callable", E_USER_WARNING);

            return;
        }

        $this->functions[current($this->revisions)][] = $callable;
    }

    /**
     * Add a data source to module for existence and up to date checking
     *
     * @param string $dsn   Name of the data source
     * @param string $query Data source is considered up to date if the returns a result
     *
     * @return void
     */
    function addDatasource($dsn, $query)
    {
        $this->datasources[$dsn][] = $query;
    }

    /**
     * @param string $dsn
     *
     * @return void
     */
    public function addElasticIndexDependency(string $dsn, ElasticObject $class): void
    {
        $revision = current($this->revisions);

        $this->elastic_dependencies[$revision] = $this->elastic_dependencies[$revision] ?? [];

        $this->elastic_dependencies[$revision][$dsn] = $class;
    }

    /**
     * Check all declared data sources and retrieve them as uptodate or obsolete
     *
     * @return array The up to date and obsolete DSNs
     */
    function getDatasources()
    {
        $dsns = [];
        foreach ($this->datasources as $dsn => $_queries) {
            if ($ds = @CSQLDataSource::get($dsn)) {
                foreach ($_queries as $_query) {
                    $dsns[$ds->loadResult($_query) ? "uptodate" : "obsolete"][] = [$dsn, $_query];
                }
            } else {
                $dsns["unavailable"][] = [$dsn, ""];
            }
        }

        return $dsns;
    }


    /**
     * @return array
     */
    public function getElasticDependencies(): array
    {
        $dependencies = [];
        foreach ($this->elastic_dependencies as $_elastic_dep) {
            $dependencies = array_merge($dependencies, array_keys($_elastic_dep));
        }

        return $dependencies;
    }

    public function getElasticClassForDsn(string $dsn): ?ElasticObject
    {
        foreach ($this->elastic_dependencies as $_elastic_dep) {
            if (array_key_exists($dsn, $_elastic_dep)) {
                return $_elastic_dep[$dsn];
            }
        }

        return null;
    }

    /**
     * Associates an SQL query to a module revision
     *
     * @param string $query         SQL query
     * @param bool   $ignore_errors Ignore errors if true
     * @param string $dsn           Data source name
     *
     * @return void
     */
    function addQuery($query, $ignore_errors = false, $dsn = null)
    {
        // Table creation ?
        $matches = [];
        // Use capturing group for "if not exists" clause, if so, error will be thrown if table already exists
        if (
            preg_match(
                '/(?:CREATE\s+TABLE)(?<can_exist>\s+IF\s+NOT\s+EXISTS)?\s+(?<table_name>[\w`]+)/i',
                $query,
                $matches
            )
        ) {
            $table = trim($matches["table_name"], "`");

            $this->addTable($table, $dsn, !empty($matches["can_exist"]));

            if (CAppUI::conf('intercept_database_engine_instruction')) {
                $query = preg_replace('/(?<instruction>ENGINE\s*=)(?<engine>\s*[a-zA-Z]+)/i', '', $query);
            } elseif (strpos($query, "ENGINE=") === false && strpos($query, "LIKE ") === false) {
                // If no engine, but not created with "LIKE" (eg datasource_log_archive)
                $table = trim($matches["table_name"], "`");
                CModelObject::error("Table %s without engine", $table);
            }
        }

        // Table name changed ?
        $matches = [];
        if (
            preg_match(
                '/RENAME\s+TABLE\s+(?<table_name_from>[\w`]+)\s+TO\s+(?<table_name_to>[\w`]+)/i',
                $query,
                $matches
            ) || preg_match(
                '/ALTER\s+TABLE\s+(?<table_name_from>[\w`]+)\s+RENAME\s+(?<table_name_to>[\w`]+)/i',
                $query,
                $matches
            )
        ) {
            $tableFrom = trim($matches["table_name_from"], "`");
            $tableTo   = trim($matches["table_name_to"], "`");
            $this->renameTable($tableFrom, $tableTo, $dsn);
        }

        // Table removed ?
        $matches = [];
        if (preg_match('/DROP\s+TABLE(?<exists>\s+IF\s+EXISTS)?\s+(?<table_name>[\w`]+)/i', $query, $matches)) {
            $table = trim($matches["table_name"], "`");
            $this->dropTable($table, $dsn);
        }

        $this->queries[current($this->revisions)][] = [$query, $ignore_errors, $dsn];
    }

    /**
     * Add a preference query to current revision definition
     *
     * @param string $name    Name of the preference
     * @param string $default Default value of the preference
     *
     * @return void
     */
    function addPrefQuery($name, $default)
    {
        $this->preferences[current($this->revisions)][] = [$name, $default, false];
    }

    /**
     * Delete a user preference
     *
     * @param string $name Name of the preference
     *
     * @return void
     */
    function delPrefQuery($name)
    {
        return;

        // FIXME: les fonctions addPrefQuery et delPrefQuery sont EXECUTEES
        // a CHAQUE fois quon va sur la page de setup ! cf. pure SQL
        $pref         = new CPreferences();
        $where        = [];
        $where['key'] = " = '$name'";
        foreach ($pref->loadList($where) as $_pref) {
            if ($msg = $_pref->delete()) {
                CAppUI::setMsg($msg, UI_MSG_ERROR);
            }
        }
    }

    /**
     * Add a functional permission query to current revision definition
     *
     * @param string $name    Name of the functional permission
     * @param string $default Default value of the functional permission
     *
     * @return void
     */
    function addFunctionalPermQuery($name, $default)
    {
        $this->preferences[current($this->revisions)][] = [$name, $default, true];
    }

    /**
     * Registers a table in the module
     *
     * @param string $table    Table name
     * @param string $dsn      Datasource name
     * @param bool   $canExist Flag wether table can exist prior to the creation request, no error is thrown if true
     *
     * @return void
     */
    function addTable($table, $dsn = null, $canExist = false)
    {
        if (array_key_exists($dsn, $this->tables) && in_array($table, $this->tables[$dsn])) {
            if (!$canExist) {
                CModelObject::error("Table-table%s-already-exists", $table);
            }
        } else {
            $this->tables[$dsn][] = $table;
        }
    }

    /**
     * Remove a table in the module
     *
     * @param string $table Table name
     * @param string $dsn   Datasource name
     *
     * @return void
     */
    function dropTable($table, $dsn = null)
    {
        if (array_key_exists($dsn, $this->tables)) {
            CMbArray::removeValue($table, $this->tables[$dsn]);
        }
    }

    /**
     * Change a table name in the module
     *
     * @param string $tableFrom Table former name
     * @param string $tableTo   Table latter name
     * @param string $dsn       Datasource name
     *
     * @return void
     */
    function renameTable($tableFrom, $tableTo, $dsn = null)
    {
        $this->dropTable($tableFrom, $dsn);
        $this->addTable($tableTo, $dsn);
    }

    /**
     * Adds a revision dependency with another module
     *
     * @param string $module   The dependency name
     * @param string $revision The dependency revision
     *
     * @return void
     */
    function addDependency($module, $revision)
    {
        $dependency                                      = new stdClass();
        $dependency->module                              = $module;
        $dependency->revision                            = $revision;
        $this->dependencies[current($this->revisions)][] = $dependency;
    }

    /**
     * Adds default configuration, based on old configurations
     *
     * @param string $new_path New config path
     * @param string $old_path Current config path, if different
     * @param string $value    Force default value, if no configuration path is available
     *
     * @return void
     */
    function addDefaultConfig($new_path, $old_path = null, $value = null)
    {
        if (!$old_path) {
            $old_path = $new_path;
        }

        $config_value = @CAppUI::conf($old_path);

        if ($config_value === null) {
            if ($value === null) {
                return;
            }

            $config_value = $value;
        }

        if ($config_value === false) {
            $config_value = 0;
        }

        $query = "INSERT INTO `configuration` (`feature`, `value`) VALUES (?1, ?2)";
        $query = $this->ds->prepare($query, $new_path, $config_value);
        $this->addQuery($query);
    }

    /**
     * Adds default configuration, based on old configurations
     *
     * @param string $key   CConfiguration key
     * @param string $value CConfiguration value
     *
     * @return void
     */
    function addDefaultTextCConfiguration($key, $value)
    {
        if (!$key || ($value === null) || !is_string($value)) {
            return;
        }

        $query = "SELECT `configuration_id` FROM `configuration` WHERE `feature` = ?1 AND `object_class` IS NULL AND `object_id` IS NULL";
        $query = $this->ds->prepare($query, $key);

        $id = $this->ds->loadResult($query);

        if ($id) {
            $query = "UPDATE `configuration` SET `value` = ?1 WHERE `configuration_id` = ?2";
            $query = $this->ds->prepare($query, $value, $id);
        } else {
            $query = "INSERT INTO `configuration` (`feature`, `value`, `object_class`, `object_id`) VALUES (?1, ?2, NULL, NULL)";
            $query = $this->ds->prepare($query, $key, $value);
        }

        $this->ds->exec($query);
    }

    /**
     * Migrate an old instance configuration to a static CCOnfiguration
     *
     * @param string $old_path
     * @param string $new_path
     *
     * @return void
     * @throws Exception
     */
    protected function migrateInstanceConfigToStatic(string $old_path, string $new_path): void
    {
        $this->insertStaticConfiguration($new_path, @CAppUI::conf($old_path));
    }

    //  /**
    //   * Migrate a global CConfiguration to a static one
    //   *
    //   * Todo: Handle case when a global configuration already exists (just update the "static" column)
    //   *
    //   * @param string $old_path
    //   * @param string $new_path
    //   *
    //   * @return void
    //   * @throws Exception
    //   */
    //  protected function migrateGlobalCConfigurationToStatic(string $old_path, string $new_path): void {
    //    $this->insertStaticConfiguration($new_path, @CAppUI::conf($old_path, 'global'));
    //  }

    /**
     * Insert a static configuration
     *
     * @param string     $path
     * @param mixed|null $value
     *
     * @return void
     */
    private function insertStaticConfiguration(string $path, $value = null): void
    {
        if ($value === null) {
            // In order to not trigger an empty revision error
            $this->addQuery('SELECT 1');

            return;
        }

        if ($value === false) {
            $value = 0;
        } elseif ($value === true) {
            $value = 1;
        }

        $query = "INSERT INTO `configuration` (`feature`, `value`, `static`) VALUES (?1, ?2, '1')";
        $query = $this->ds->prepare($query, $path, $value);
        $this->addQuery($query);
    }

    /**
     * Tells if we are still in the old preferences system
     *
     * @param bool $core_upgrade True if core upgrading (after initial install)
     *
     * @return bool
     */
    static function isOldPrefSystem($core_upgrade = false)
    {
        if (self::$_old_pref_system === null || $core_upgrade) {
            $ds = CSQLDataSource::get("std");

            self::$_old_pref_system = $ds->loadField("user_preferences", "pref_name") != null;
        }

        return self::$_old_pref_system;
    }

    /**
     * Tells if we are in the new preference system, but without the "restricted" field
     *
     * @return bool
     */
    static function isOldPrefSystemRestricted()
    {
        if (self::$_old_pref_system_restricted === null) {
            $ds = CSQLDataSource::get("std");

            self::$_old_pref_system_restricted = $ds->loadField("user_preferences", "restricted") == null;
        }

        return self::$_old_pref_system_restricted;
    }

    /**
     * Launches module upgrade process
     *
     * @param CModule $module       Module object
     * @param bool    $core_upgrade True if it's a core module upgrade
     * @param string  $stopRevision Upgrade only to a specific revision
     *
     * @return string|null New revision, null on error
     * @throws Exception
     */
    function upgrade($module, $core_upgrade = false, $stopRevision = null)
    {
        if (array_key_exists($this->mod_version, $this->queries)) {
            CAppUI::setMsg("Latest revision '%s' should not have upgrade queries", UI_MSG_ERROR, $this->mod_version);
            return;
        }

        CApp::setTimeLimit(3600);

        $oldRevision = $module->mod_version;

        if (null !== $stopRevision && SemverComparator::greaterThanOrEqualTo($oldRevision, $stopRevision)) {
            return;
        }

        if (
            !array_key_exists($oldRevision, $this->queries)
            && !array_key_exists($oldRevision, $this->preferences)
            && !array_key_exists($oldRevision, $this->functions)
            && !array_key_exists($oldRevision, $this->dependencies)
            && !array_key_exists($oldRevision, $this->config_moves)
            && !array_key_exists($oldRevision, $this->registered_keys)
        ) {
            CAppUI::setMsg(
                "No queries, preferences, functions, dependencies, config moves, or registered keys for '%s' setup at revision '%s'",
                CAppUI::UI_MSG_WARNING,
                $this->mod_name,
                $oldRevision
            );

            return;
        }

        // Point to the current revision
        reset($this->revisions);
        while ($oldRevision != $currRevision = current($this->revisions)) {
            next($this->revisions);
        }

        $depFailed        = false;
        $module->mod_type = $this->mod_type;

        do {
            $currRevision = current($this->revisions);

            $module->mod_version = $currRevision;

            // Stop executing update when stopRevision is reached
            if (null !== $stopRevision && SemverComparator::greaterThan($currRevision, $stopRevision)) {
                $module->store();
                return;
            }

            self::$current = $currRevision;

            if (!empty($this->dependencies[$currRevision])) {
                foreach ($this->dependencies[$currRevision] as $dependency) {
                    $_module = @CModule::getInstalled($dependency->module);

                    if (!$_module || SemverComparator::greaterThan($dependency->revision, $_module->mod_version)) {
                        $depFailed = true;
                        CAppUI::setMsg(
                            "Failed module dependency for '%s' at revision '%s'",
                            CAppUI::UI_MSG_WARNING,
                            $dependency->module,
                            $dependency->revision
                        );
                    }
                }
            }

            if ($depFailed) {
                $module->store();
                return $currRevision;
            }

            // Query upgrading
            if (!empty($this->queries[$currRevision])) {
                foreach ($this->queries[$currRevision] as $_query) {
                    [$query, $ignore_errors, $dsn] = $_query;

                    $ds = ($dsn ? CSQLDataSource::get($dsn) : $this->ds);

                    if (!$ds->exec($query)) {
                        if ($ignore_errors) {
                            CAppUI::setMsg("Errors ignored for revision '%s'", UI_MSG_OK, $currRevision);
                            continue;
                        }
                        CAppUI::setMsg("Error in queries for revision '%s': see logs", UI_MSG_ERROR, $currRevision);

                        $module->store();

                        return $currRevision;
                    }
                }
            }

            // Callback upgrading
            if (!empty($this->functions[$currRevision])) {
                foreach ($this->functions[$currRevision] as $function) {
                    if (!call_user_func($function)) {
                        $function_name = get_class($function[0]) . "->" . $function[1];
                        CAppUI::setMsg(
                            "Error in function '%s' call back for revision '%s': see logs",
                            UI_MSG_ERROR,
                            $function_name,
                            $currRevision
                        );

                        $module->store();

                        return $currRevision;
                    }
                }
            }

            // Preferences
            $ds = $this->ds;
            if (!empty($this->preferences[$currRevision])) {
                foreach ($this->preferences[$currRevision] as $_pref) {
                    [$_name, $_default, $_restricted] = $_pref;

                    // Former pure SQL system
                    // Cannot check against module version or fresh install will generate errors
                    if (self::isOldPrefSystem($core_upgrade)) {
                        $query  = "SELECT * FROM `user_preferences` WHERE `pref_user` = '0' AND `pref_name` = '$_name'";
                        $result = $ds->exec($query);

                        if (!$ds->numRows($result)) {
                            $query = "INSERT INTO `user_preferences` (`pref_user` , `pref_name` , `pref_value`)
                  VALUES ('0', '$_name', '$_default');";
                            $ds->exec($query);
                        }
                    } // New preference system, but without the "restricted" field
                    elseif (self::isOldPrefSystemRestricted()) {
                        $query  = "SELECT * FROM `user_preferences` WHERE `user_id` IS NULL AND `key` = '$_name'";
                        $result = $ds->exec($query);

                        if (!$ds->numRows($result)) {
                            $query = "INSERT INTO `user_preferences` (`user_id` , `key` , `value`)
                  VALUES (NULL, '$_name', '$_default');";
                            $ds->exec($query);
                        }
                    } // Latter object oriented system
                    else {
                        $pref = new CPreferences();

                        $where            = [];
                        $where["user_id"] = " IS NULL";
                        $where["key"]     = " = '$_name'";

                        if (!$pref->loadObject($where)) {
                            $pref->key        = $_name;
                            $pref->value      = $_default;
                            $pref->restricted = ($_restricted) ? "1" : "0";
                            $pref->store();
                        }
                    }
                }
            }

            // Config moves
            if (!empty($this->config_moves[$currRevision])) {
                foreach ($this->config_moves[$currRevision] as $config) {
                    if (is_array($config) && count($config) > 1) {
                        CAppUI::setConf($config[1], CAppUI::conf($config[0]));
                    }
                }
            }

            if (!empty($this->registered_keys[$currRevision])) {
                $key_builder = new KeyBuilder();

                /** @var Key $_key */
                foreach ($this->registered_keys[$currRevision] as $_key) {
                    $key_builder->persistMetadata($_key->getName(), $_key->getAlg(), $_key->getMode());
                }
            }

            $module->mod_version = $currRevision;
            $module->store();
        } while (next($this->revisions));

        if (SemverComparator::equalTo($currRevision, end($this->revisions))) {
            $module->mod_version = $this->mod_version;
            $module->store();
            return $module->mod_version;
        }

        return;
    }

    /**
     * Removes a module
     * Warning, it actually breaks module dependency
     *
     * @return boolean Job done
     */
    function remove()
    {
        if ($this->mod_type == "core") {
            CAppUI::setMsg("Impossible de supprimer le module '%s'", UI_MSG_ERROR, $this->mod_name);

            return false;
        }

        $success = true;
        foreach ($this->tables as $_dsn => $_tables) {
            $ds = $_dsn ? CSQLDataSource::get($_dsn) : $this->ds;
            foreach ($_tables as $table) {
                $query = "DROP TABLE `$table`";
                if (!$ds->exec($query)) {
                    $success = false;
                    CAppUI::setMsg("Failed to remove table '%s'", UI_MSG_ERROR, $table);
                }
            }
        }

        return $success;
    }

    /**
     * Link to the configure pane. Should be handled in the template
     *
     * @return void
     */
    function configure()
    {
        CAppUI::redirect("m=$this->mod_name&a=configure");

        return true;
    }

    /**
     * Move the configuration setting for a given path in a new configuration
     *
     * @param string $old_path Tokenized path, eg "module class var";
     * @param string $new_path Tokenized path, eg "module class var";
     *
     * @return void
     */
    function moveConf($old_path, $new_path)
    {
        $this->config_moves[current($this->revisions)][] = [$old_path, $new_path];
    }

    /**
     * Todo: Does not handle User Actions & Mandatory Constraints
     * Rename a field in the user log
     *
     * @param string $object_class object_class value of the user_log
     * @param string $from         The field to rename
     * @param string $to           The new name
     *
     * @return void
     */
    function getFieldRenameQueries($object_class, $from, $to)
    {
        // CUserLog
        $query =
            "UPDATE `user_log` 
       SET   
         `fields` = '$to', 
         `extra`  = REPLACE(`extra`, '\"$from\":', '\"$to\":')
       WHERE 
         `object_class` = '$object_class' AND 
         `fields` = '$from' AND 
         `type` IN('store', 'merge')";
        $this->addQuery($query);

        $query =
            "UPDATE `user_log` 
       SET   
         `fields` = REPLACE(`fields`, ' $from ', ' $to '), 
         `fields` = REPLACE(`fields`, '$from ' , '$to '), 
         `fields` = REPLACE(`fields`, ' $from' , ' $to'), 
         `extra`  = REPLACE(`extra`, '\"$from\":', '\"$to\":')
       WHERE 
         `object_class` = '$object_class' AND 
         `fields` LIKE '%$from%' AND 
         `type` IN('store', 'merge')";
        $this->addQuery($query);

        // CExClassHostField
        $query =
            "UPDATE `ex_class_host_field`
       SET
         `field` = '$to'
       WHERE
         `host_class` = '$object_class' AND
         `field` = '$from'";
        $this->addQuery($query);

        // CExClassConstraint
        $query =
            "UPDATE `ex_class_constraint`
       SET
         `field` = REPLACE(`field`, '.$object_class-$from', '.$object_class-$to')
       WHERE
         `field` LIKE '%.$object_class-$from%'";
        $this->addQuery($query);

        $query =
            "UPDATE `ex_class_constraint`
       LEFT JOIN `ex_class_event` ON `ex_class_event`.`ex_class_event_id` = `ex_class_constraint`.`ex_class_event_id`
       SET
         `field` = '$to'
       WHERE
         `field` = '$from' AND
         `ex_class_event`.`host_class` = '$object_class'";
        $this->addQuery($query);

        $query =
            "UPDATE `ex_class_constraint`
       LEFT JOIN `ex_class_event` ON `ex_class_event`.`ex_class_event_id` = `ex_class_constraint`.`ex_class_event_id`
       SET
         `field` = REPLACE(`field`, '$from.', '$to.')
       WHERE
         `field` LIKE '$from.%' AND
         `ex_class_event`.`host_class` = '$object_class'";
        $this->addQuery($query);
    }

    /**
     * Update table status
     *
     * @param string $table Table name
     *
     * @return void
     */
    function updateTableStatus($table)
    {
        $now   = CMbDT::dateTime();
        $query = "UPDATE `table_status`
       SET `update_time` = '$now'
       WHERE `name` = '$table';";
        $this->addQuery($query);
    }

    /**
     * @param string $module_name
     *
     * @return bool|string
     */
    static function getCSetupClass($module_name)
    {
        $module_name = strpos($module_name, "dP", 0) === 0 ? substr($module_name, 2) : $module_name;
        $module_name = ucfirst($module_name);
        $class_name  = "CSetup$module_name";

        return class_exists($class_name) ? $class_name : false;
    }

    /**
     * Return true if the given table exists
     *
     * @param string      $table Table name
     * @param string|null $dsn   DSN
     *
     * @return bool
     * @throws CMbException
     */
    public function tableExists($table, $dsn = null)
    {
        $ds = ($dsn) ? CSQLDataSource::get($dsn) : $this->ds;

        if (!$ds) {
            throw new CMbException('CSQLDatasource-error-%s is not a SQL datasource');
        }

        $exists = $ds->loadTable($table);

        return (($exists !== null) && ($exists !== false) && ($exists == $table));
    }

    /**
     * Return true if the given field exists on the table
     *
     * @param string      $table Table name
     * @param string      $field Column name
     * @param string|null $dsn   DSN
     *
     * @return bool
     * @throws CMbException
     */
    public function columnExists($table, $field, $dsn = null)
    {
        $ds = ($dsn) ? CSQLDataSource::get($dsn) : $this->ds;

        if (!$ds) {
            throw new CMbException('CSQLDatasource-error-%s is not a SQL datasource');
        }

        if (!$this->tableExists($table)) {
            return false;
        }

        $exists = $ds->loadField($table, $field);

        return (($exists !== null) && ($exists !== false) && ($exists == $field));
    }

    /**
     * Set category and package to a module
     *
     * @param string $category Category name
     * @param string $package  Package name
     */
    protected function setModuleCategory($category, $package)
    {
        $query = "UPDATE `modules`
                SET `mod_category` = '$category',
                    `mod_package` = '$package'
                WHERE `mod_name` = '$this->mod_name';";
        $this->addQuery($query);
    }

    /**
     * Move default configuration from a path to another
     *
     * @param string $old_path Old path
     * @param string $new_path New path
     */
    protected function moveConfiguration(string $old_path, string $new_path): void
    {
        $query = "UPDATE `configuration` SET `feature` = '$new_path' WHERE `feature` = '$old_path'";

        $this->addQuery($query);
    }

    /**
     * Update an old value of a configuration
     *
     * @param string $path
     * @param string $old_value
     * @param string $new_value
     *
     * @return void
     */
    protected function updateConfigurationValue(string $path, string $old_value, string $new_value): void
    {
        $query = "UPDATE `configuration`
                  SET `value` = '$new_value'
                  WHERE `feature` = '$path'
                  AND `value` = '$old_value'";

        $this->addQuery($query);
    }

    /**
     * @param string        $name
     * @param Alg           $alg
     * @param Mode          $mode
     * @param callable|null $callback
     *
     * @throws CouldNotPersistKey
     */
    public function registerSymmetricKey(string $name, Alg $alg, Mode $mode): void
    {
        if (array_key_exists($name, $this->registered_keys[current($this->revisions)])) {
            throw CouldNotPersistKey::alreadyExists($name);
        }

        $this->registered_keys[current($this->revisions)][$name] = Key::createEmpty($name, $alg, $mode);
    }
}
