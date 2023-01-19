<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Components\Cache\Exceptions\CouldNotGetCache;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbMetaObjectPolyfill;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CRequest;
use Ox\Core\CStoredObject;
use Ox\Core\Mutex\CMbFileMutex;
use Ox\Mediboard\System\Forms\CExObject;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * General purpose configuration
 */
class CConfiguration extends CMbObject
{
    const INHERIT = "@@INHERIT@@";

    const STATUS_OK    = "ok";
    const STATUS_DIRTY = "dirty";
    const STATUS_EMPTY = "empty";

    const GET_VALUES_CACHE_STATUS_CACHE = 'CConfiguration.getValuesCacheStatus';

    public const RESOURCE_TYPE = "configuration";

    /** @var int Primary key */
    public $configuration_id;

    /** @var string Name of the configuration */
    public $feature;

    /** @var string Value of the configuration */
    public $value;

    /** @var string Alternative value of the configuration (multiple environments purposes) */
    public $alt_value;

    public $object_class;
    public $object_id;

    /** @var bool */
    public $static;

    public $_ref_object;

    // The BIG config model
    private static $model_raw = [];
    private static $model     = [];

    private static $values = [];
    private static $hosts  = [];

    private static $models_infos = [];

    private static $dirty = [];

    private static $cache = [];

    /**
     * @inheritdoc
     */
    function getSpec()
    {
        $spec                     = parent::getSpec();
        $spec->key                = "configuration_id";
        $spec->table              = "configuration";
        $spec->uniques["feature"] = ["feature", "object_class", "object_id"];

        return $spec;
    }

    /**
     * @inheritdoc
     */
    function getProps()
    {
        $props                 = parent::getProps();
        $props["feature"]      = "str notNull fieldset|default";
        $props["value"]        = "str fieldset|default";
        $props["alt_value"]    = "str";
        $props["object_id"]    = "ref class|CMbObject meta|object_class cascade back|configurations"; // not notNull
        $props["object_class"] = "str class show|0"; // not notNull
        $props['static']       = 'bool notNull default|0 fieldset|default';

        return $props;
    }

    /**
     * @inheritdoc
     */
    function updateFormFields()
    {
        parent::updateFormFields();

        $this->_view = "[$this->feature] - ";

        if ($this->object_class && $this->object_id) {
            $this->_view .= "$this->object_class-$this->object_id";
        } else {
            $this->_view .= "global";
        }
    }

    /**
     * @inheritdoc
     */
    function store()
    {
        if ($msg = parent::store()) {
            return $msg;
        }

        $module_name = explode(' ', $this->feature);
        //CTableStatus::change($this->getSpec()->table);
        CConfiguration::updateTableStatus($module_name[0], CMbDT::dateTime());

        return null;
    }

    /**
     * @inheritdoc
     */
    function delete()
    {
        $module_name = explode(' ', $this->feature);
        if ($msg = parent::delete()) {
            return $msg;
        }

        CConfiguration::updateTableStatus($module_name[0], CMbDT::dateTime());

        //CTableStatus::change($this->getSpec()->table);

        return null;
    }

    /**
     * @return bool
     */
    public function isStatic(): bool
    {
        return (bool)$this->static;
    }

    /**
     * Returns the CMbObjectSpec object of the host object
     *
     * @return CMbObjectSpec
     */
    static function _getSpec()
    {
        static $spec;

        if (!isset($spec)) {
            $self = new self;
            $spec = $self->_spec;
        }

        return $spec;
    }

    /**
     * Register a configuration tree
     *
     * @param array $configs The config tree with the specs
     *
     * @return void
     */
    static function register($configs)
    {
        // Standard mode
        foreach ($configs as $_ctx => $_cfg) {
            $modules = array_keys($_cfg);
            foreach ($modules as $_module) {
                if (isset(self::$model_raw[$_module]) && isset(self::$model_raw[$_module][$_ctx])) {
                    self::$model_raw[$_module][$_ctx] = array_merge_recursive(self::$model_raw[$_module][$_ctx], $_cfg);
                    continue;
                }
                self::$model_raw[$_module][$_ctx] = $_cfg;
            }
        }

        // New mode
        CConfigurationModelManager::setRawModel(static::$model_raw);
    }

    /**
     * Build the configuration tree
     *
     * @param string $module Name of the module to get tree for
     *
     * @return void
     */
    static protected function buildTree($module)
    {
        if (isset(self::$model_raw[$module])) {
            foreach (self::$model_raw[$module] as $_inherit => $_tree) {
                $list = [];
                self::_buildConfigs($list, [], $_tree);

                if (!isset(self::$model[$module][$_inherit])) {
                    self::$model[$module][$_inherit] = [];
                }

                self::$model[$module][$_inherit] = array_merge(self::$model[$module][$_inherit], $list);
            }
        } else {
            self::$model_raw[$module] = [];
        }
    }

    /**
     * Build configuration subtree
     *
     * @param array $list List to fill
     * @param array $path Configuration path
     * @param array $tree Model tree
     *
     * @return void
     */
    protected static function _buildConfigs(&$list, $path, $tree)
    {
        foreach ($tree as $key => $subtree) {
            $_path   = $path;
            $_path[] = $key;

            // If a leaf (prop)
            if (is_string($subtree)) {
                // Build spec
                $parts = explode(" ", $subtree);

                $spec_options = [
                    "type"   => array_shift($parts),
                    "string" => $subtree,
                ];

                foreach ($parts as $_part) {
                    $options                             = explode("|", $_part, 2);
                    $spec_options[array_shift($options)] = count($options) ? $options[0] : true;
                }

                // Always have a default value
                if (!isset($spec_options["default"])) {
                    $spec_options["default"] = "";
                }

                $list[implode(" ", $_path)] = $spec_options;
            } // ... else a subtree
            else {
                self::_buildConfigs($list, $_path, $subtree);
            }
        }
    }

    /**
     * Get hash of the data
     *
     * @param array $data The data to get the hash of (MD5 of serialization)
     *
     * @return string
     */
    static protected function _getHash($data)
    {
        return md5(serialize($data));
    }

    /**
     * Get model cache status
     *
     * @param string $module Name of the module to get model for
     *
     * @return string Can be self::STATUS_EMPTY, self::STATUS_DIRTY or self::STATUS_OK
     */
    static function getModelCacheStatus($module)
    {
        $cache = Cache::getCache(Cache::OUTER);

        if (!isset(self::$models_infos[$module])) {
            self::$models_infos[$module] = $cache->get("config-module-$module");
        }

        if (!self::$models_infos[$module]) {
            return self::STATUS_EMPTY;
        }

        if (self::_isModelCacheDirty(self::$models_infos[$module]["hash"], $module)) {
            return self::STATUS_DIRTY;
        }

        return self::STATUS_OK;
    }

    /**
     * Get model cache update date
     *
     * @param string $module Name of the module to get model for
     *
     * @return null|string Model cache date or null if no cache
     */
    static protected function _getModelCacheDate($module)
    {
        $cache = Cache::getCache(Cache::OUTER);

        if (!isset(self::$models_infos[$module])) {
            self::$models_infos[$module] = $cache->get("config-module-$module");
        }


        if (self::$models_infos[$module]) {
            return self::$models_infos[$module]["date"];
        }

        return null;
    }

    /**
     * Tells if the model cache is out of date
     *
     * @param string $hash   The hash of the model in cache
     * @param string $module Name of the module to check model for
     *
     * @return bool True if cache is out of date
     */
    static protected function _isModelCacheDirty($hash, $module)
    {
        return (isset(self::$model_raw[$module])) ? $hash !== self::_getHash(self::$model_raw[$module]) : 0;
    }

    /**
     * Tells if the values cache is out of date
     *
     * @param string $date   The date of the latest data update
     * @param string $module Name of the module we check the cache's status for
     *
     * @return bool True if the values cache is out of date
     */
    static protected function _isValuesCacheDirty($date, $module)
    {
        if (isset(self::$dirty[$module]) && self::$dirty[$module] === false) {
            return false;
        }

        $model_status = self::getModelCacheStatus($module);

        if ($model_status !== self::STATUS_OK) {
            return self::$dirty[$module] = true;
        }

        $spec = self::_getSpec();

        if ((new CTableStatus())->isInstalled()) {
            $status_result = CTableStatus::getInfo($spec->table, $module);

            self::$dirty[$module] = $date <= $status_result["update_time"] ||
                $date < self::_getModelCacheDate($module);
        } else {
            self::$dirty[$module] = $date < self::_getModelCacheDate($module);
        }

        // database or model were updated
        return self::$dirty[$module];
    }

    /**
     * Get the up to date model
     *
     * @param string $module   Name of the module to get model of
     * @param array  $inherits An optional selection of inheritance paths
     *
     * @return array The up to date model
     */
    static function getModel($module, $inherits = [])
    {
        $cache = Cache::getCache(Cache::OUTER);

        if (empty(self::$model[$module])) {
            if (($model = $cache->get("config-module-$module")) && !self::_isModelCacheDirty($model["hash"], $module)) {
                self::$model[$module] = $model["content"];
            } else {
                self::buildTree($module);

                $cache->set(
                    "config-module-$module",
                    [
                        "date"    => CMbDT::strftime(CMbDT::ISO_DATETIME),
                        // Don't use CMbDT::dateTime because it may be offsetted
                        "hash"    => self::_getHash(self::$model_raw[$module]),
                        "content" => (isset(self::$model[$module])) ? self::$model[$module] : [],
                    ]
                );
            }
        }

        if (!empty($inherits)) {
            if (!is_array($inherits)) {
                $inherits = [$inherits];
            }

            $subset = [];
            foreach ($inherits as $_inherit) {
                $subset[$_inherit] = isset(self::$model[$module][$_inherit]) ? self::$model[$module][$_inherit] : [];
            }

            return $subset;
        }

        return (isset(self::$model[$module])) ? self::$model[$module] : [];
    }

    /**
     * Build all the configuration data
     *
     * @param string $module Name of the module to build config for
     *
     * @return void
     * @deprecated
     *
     */
    static function buildAllConfig($module)
    {
        $inherits = array_keys(self::getModel($module));

        $values_flat = [];

        foreach ($inherits as $_inherit) {
            $tree = self::getObjectTree($_inherit);
            $all  = [];
            self::_flattenObjectTree($all, $tree);


            $values_flat["global"] = self::getConfigs($_inherit, $module, null, null);
            foreach ($all as $_object) {
                $_guid = $_object->_guid;

                if (!isset($values_flat[$_guid])) {
                    $values_flat[$_guid] = [];
                }

                $values_flat[$_guid] = array_merge(
                    $values_flat[$_guid],
                    self::getConfigs($_inherit, $module, null, $_object)
                );
            }
        }

        $values = [];
        $hosts  = [];
        foreach ($values_flat as $_host => $_values) {
            $values[$_host] = self::unflatten($_values);
            $hosts[]        = $_host;
        }

        self::$hosts           = $hosts;
        self::$values[$module] = $values;
    }

    /**
     * Unflatten a dictionnary
     *
     * @param array  $array     The dictionnary
     * @param string $separator The separator
     *
     * @return array
     */
    static function unflatten(array $array, $separator = " ")
    {
        $tree = [];

        foreach ($array as $_key => $_value) {
            $_parts = explode($separator, $_key);

            $node =& $tree;
            foreach ($_parts as $_part) {
                $node =& $node[$_part];
            }

            $node = $_value;
        }

        return $tree;
    }

    /**
     * Refresh the data cache
     *
     * @param string $module Name of the module data cache is refreshed for
     *
     * @return void
     * @throws Exception|InvalidArgumentException
     * @deprecated
     *
     */
    static function refreshDataCache($module)
    {
        $mutex = new CMbFileMutex("config-build-$module");
        $mutex->acquire(20);

        $cache = Cache::getCache(Cache::OUTER);

        // If cache was built by another thread
        if (self::getValuesCacheStatus($module) === self::STATUS_OK) {
            $mutex->release();

            $hosts_shm = $cache->get("config-values-$module-__HOSTS__");
            $hosts     = $hosts_shm["content"];

            if (!$module) {
                $module = 'none';
            }

            $values = [];
            foreach ($hosts as $_host) {
                $_host_value    = $cache->get("config-values-$module-$_host");
                $values[$_host] = $_host_value["content"];
            }

            self::$values[$module] = $values;
            self::$hosts           = $hosts;

            self::$dirty[$module] = false;

            return;
        }

        $t = microtime(true);

        self::buildAllConfig($module);

        $t1 = microtime(true) - $t;

        $datetime = CMbDT::strftime(CMbDT::ISO_DATETIME);

        if (!$module) {
            $module = 'none';
        }

        foreach (self::$values[$module] as $_host => $_configs) {
            $cache->set("config-values-$module-$_host", ['content' => $_configs]);
        }

        $cache->set(
            "config-values-$module-__HOSTS__",
            [
                "date"    => $datetime,
                "content" => array_keys(self::$values[$module]),
            ]
        );

        $t2 = microtime(true) - $t - $t1;

        CApp::log(
            "Log from CConfiguration",
            sprintf("'config-values-%s-*' generated in %f ms, written in %f ms", $module, $t1 * 1000, $t2 * 1000)
        );

        $mutex->release();

        self::$dirty[$module] = false;
    }

    /**
     * Clear data cache
     *
     * @param array|string $module Array containing the name of the modules to clear
     *
     * @return void
     */
    static function clearDataCache($module = null)
    {
        if (!$module) {
            Cache::deleteKeys(Cache::OUTER, "config-");
            Cache::deleteKeys(Cache::OUTER, 'CConfiguration.getValuesCacheStatus-');
            self::$values = [];
        } else {
            if (is_array($module)) {
                foreach ($module as $_mod) {
                    self::clearModuleCache($_mod);
                }
            } else {
                self::clearModuleCache($module);
            }
        }

        self::$hosts = [];
    }

    /**
     * Clear the datacache for a module
     *
     * @param string $module Name of the module the cache must be clear for
     *
     * @return void
     */
    protected static function clearModuleCache($module)
    {
        $shm = Cache::getCache(Cache::OUTER);
        $shm->delete("config-module-$module");

        Cache::deleteKeys(Cache::OUTER, "config-values-$module-");

        self::$values[$module] = [];

        $cache = new Cache(self::GET_VALUES_CACHE_STATUS_CACHE, $module, Cache::INNER_OUTER, 60);
        $cache->rem();
    }

    /**
     * Get the config values for an object or for all objects
     *
     * @param string $object_guid The object to get config values of
     * @param string $module      Name of the module to get config values of
     *
     * @return array|mixed The config values
     * @deprecated
     *
     */
    static function getValues($object_guid, $module)
    {
        static $dirty = [];
        if (!isset($dirty[$module])) {
            $dirty[$module] = null;
        }

        $cache = Cache::getCache(Cache::OUTER);

        // Check values status, only once per request
        if ($dirty[$module] === null && self::getValuesCacheStatus($module) === self::STATUS_DIRTY) {
            self::refreshDataCache($module);
            $dirty[$module] = false;
        }

        // Check if all the keys exist
        if (empty(self::$hosts)) {
            $hosts = $cache->get("config-values-$module-__HOSTS__");

            if (empty($hosts)) {
                self::refreshDataCache($module);
            } else {
                self::$hosts = $hosts["content"];
            }
        }

        // For a single host
        if (empty(self::$values[$module][$object_guid])) {
            $_values = $cache->get("config-values-$module-$object_guid");

            if (empty($_values)) {
                self::refreshDataCache($module);
            } else {
                if (!isset(self::$values[$module][$object_guid])) {
                    self::$values[$module][$object_guid] = [];
                }
                self::$values[$module][$object_guid] = $_values['content'];
            }
        }

        return isset(self::$values[$module][$object_guid]) ? self::$values[$module][$object_guid] : [];
    }

    /**
     * Get a specific value or a subtree of values
     *
     * @param string $object_guid The object to get config values of
     * @param string $feature     The configuration key to get
     *
     * @return array|string The configuration value or the subtree
     */
    static function getValue($object_guid, $feature)
    {
        if (!CConfigurationModelManager::isReady()) {
            if (PHP_SAPI !== 'cli') {
                CAppUI::setMsg('CConfigurationModelManager not initialized', UI_MSG_ERROR);
            }

            return '';
        }

        $parts  = explode(' ', $feature, 2);
        $module = $parts[0];

        $object_class = $object_id = null;

        if ($object_guid !== 'global') {
            [$object_class, $object_id] = explode('-', $object_guid);
        }

        // Cache for massively requested context / features
        // /!\ SHOULD only be INNER
        $cache = static::getFeatureCache($feature, $object_guid);
        $value = $cache->get();

        if ($value !== null) {
            return $value;
        }

        try {
            $values = CConfigurationModelManager::getValues($module, $object_class, $object_id);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);

            return '';
        }

        return $cache->put(($values) ? CMbArray::readFromPath($values, $parts[1]) : '');
    }

    /**
     * @param string $path
     * @param mixed  $value
     * @param string $context
     *
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    public static function setValueInCache(string $path, $value, string $context): void
    {
        $cache = ($context === 'static') ? static::getStaticCache($path) :static::getFeatureCache($path, $context);
        $cache->put($value);
    }

    private static function getStaticCache(string $path): Cache
    {
        return new Cache('config-static-value', $path, Cache::INNER);
    }

    /**
     * @param string $path
     * @param string $context
     *
     * @return Cache
     * @throws CouldNotGetCache
     */
    private static function getFeatureCache(string $path, string $context): Cache
    {
        return new Cache("config-value-{$path}", $context, Cache::INNER);
    }

    /**
     * @param array  $paths
     * @param string $context
     *
     * @throws CouldNotGetCache
     * @throws InvalidArgumentException
     */
    public static function removeValuesFromCache(array $paths, string $context): void
    {
        if (!$paths) {
            return;
        }

        if ($context === 'static') {
            array_walk(
                $paths,
                function (&$v): void
                {
                    $v = 'config-static-value-' . $v;
                }
            );
        } else {
            array_walk(
                $paths,
                function (&$v) use ($context): void {
                    $v = "config-value-{$v}-{$context}";
                }
            );
        }


        $cache = Cache::getCache(Cache::INNER);

        $cache->deleteMultiple($paths);
    }

    /**
     * Get the values cache status
     *
     * @param string $module Name of the module the cache status will be return for
     *
     * @return string The values can be self::STATUS_EMPTY, self::STATUS_DIRTY or self::STATUS_OK
     * @deprecated
     *
     */
    static function getValuesCacheStatus($module)
    {
        $cache = new Cache(self::GET_VALUES_CACHE_STATUS_CACHE, $module, Cache::INNER_OUTER, 60);
        if ($status = $cache->get()) {
            return $status;
        }

        $shm = Cache::getCache(Cache::OUTER);

        $module_info = $shm->get("config-values-$module-__HOSTS__");

        if (!$module_info) {
            return $cache->put(self::STATUS_EMPTY);
        }

        if (self::_isValuesCacheDirty($module_info["date"], $module)) {
            return $cache->put(self::STATUS_DIRTY);
        }

        return $cache->put(self::STATUS_OK);
    }

    /**
     * Flattend an object tree
     *
     * @param array $all      The tree to flattend
     * @param array $children The children
     *
     * @return void
     * @deprecated
     *
     */
    static protected function _flattenObjectTree(&$all, $children)
    {
        $all = array_merge($all, CMbArray::pluck($children, "object"));

        foreach ($children as $child) {
            self::_flattenObjectTree($all, $child["children"]);
        }
    }

    /**
     * Get module configurations
     *
     * @param string $module  The module
     * @param string $inherit The inherit schema
     *
     * @return array
     */
    static function getModuleConfigs($module, $inherit = null)
    {
        $model = self::getModel($module, $inherit);

        if (!$module) {
            return $model;
        }

        $configs      = [];
        $module_start = "$module ";

        foreach ($model as $_inherit => $_configs) {
            $_conf = [];

            foreach ($_configs as $_feature => $_spec) {
                if (strpos($_feature, $module_start) === false) {
                    continue;
                }

                $_conf[$_feature] = $_spec;
            }

            $configs[$_inherit] = $_conf;
        }

        return $configs;
    }

    /**
     * Get the specs tree or flat array
     *
     * @param string $module      Module name
     * @param array  $config_keys Config keys to get the specs of
     * @param bool   $flatten     Flatten the specs tree or not
     *
     * @return array The specs tree or flat array
     */
    static function getConfigsSpecs($module, $config_keys = null, $flatten = true)
    {
        $configs = [];
        $model   = self::getModuleConfigs($module);

        if ($config_keys) {
            $config_keys = array_flip($config_keys);
        }

        foreach ($model as $_inherit => $_configs) {
            if ($flatten) {
                $configs = array_merge($configs, $_configs);

                if ($config_keys) {
                    $configs = array_intersect_key($configs, $config_keys);
                }
            } else {
                if ($config_keys) {
                    $_configs = array_intersect_key($_configs, $config_keys);
                }

                $configs[$_inherit] = $_configs;
            }
        }

        return $configs;
    }

    /**
     * Get the object tree
     *
     * @param string $inherit Inheritance path
     *
     * @return array The object tree
     */
    static function getObjectTree($inherit)
    {
        $tree = [];

        $inherit = self::_simplifyInherit($inherit);
        $classes = explode(" ", $inherit);

        self::_getObjectTree($tree, $classes);

        return $tree;
    }

    /**
     * Simplify an inheritance schema, removing the prefix
     *
     * @param string $inherit The inheritance schema
     *
     * @return string
     */
    static protected function _simplifyInherit($inherit)
    {
        return preg_replace('@([\w ]+ / )@', "", $inherit);
    }

    /**
     * Recursive method to build the object tree
     *
     * @param array  $subtree    Sub tree to fill
     * @param array  $classes    Classes or inheritance schema
     * @param string $parent_fwd Parent forward ref
     * @param int    $parent_id  Parent ID
     *
     * @return void
     */
    static protected function _getObjectTree(&$subtree, $classes, $parent_fwd = null, $parent_id = null)
    {
        static $cache = [];

        if (empty($classes)) {
            return;
        }

        $class  = array_pop($classes);
        $_parts = explode(".", $class);

        $fwd = null;
        if (count($_parts) === 2) {
            [$class, $fwd] = $_parts;
        }

        $where = [];
        if ($parent_fwd && $parent_id) {
            $where[$parent_fwd] = "= '$parent_id'";
        }

        $_cache_key = "$parent_fwd-$parent_id-$class";
        if (isset($cache[$_cache_key])) {
            $_list = $cache[$_cache_key];
        } else {
            /** @var CMbObject $_obj */
            $_obj = new $class;

            // Attention il faut generer les configurations de TOUS les objets, donc ne pas utiliser loadListWitfPerms
            $_list = $_obj->loadList($where);
            $_list = self::naturalSort($_list, ["_view"]);

            $cache[$_cache_key] = $_list;
        }

        if ($_list) {
            foreach ($_list as $_object) {
                $subtree[$_object->_guid] = [
                    "object"   => $_object,
                    "children" => [],
                ];

                self::_getObjectTree($subtree[$_object->_guid]["children"], $classes, $fwd, $_object->_id);
            }
        }
    }

    /**
     * Get the configuration values of an object, without inheritance
     *
     * @param string  $module       The module of the values to get
     * @param string  $object_class Object class
     * @param integer $object_id    Object ID
     * @param array   $config_keys  The keys of the values to get
     *
     * @return array The configuration values
     */
    static protected function getSelfConfig($module, $object_class = null, $object_id = null, $config_keys = null)
    {
        if (!isset(self::$cache[$module])) {
            $spec = self::_getSpec();

            $request = new CRequest();
            $request->addTable($spec->table);
            $request->addSelect(["feature", "value", "object_class", "object_id"]);

            if ($module) {
                $request->addWhere(
                    [
                        'feature' => $spec->ds->prepareLike("$module %"),
                        'value'   => 'IS NOT NULL',
                    ]
                );
            }

            self::$cache[$module] = $spec->ds->loadList($request->makeSelect());
        }

        if ($object_class && $object_id) {
            $data = array_filter(
                self::$cache[$module],
                function ($v) use ($object_class, $object_id) {
                    return $v["object_class"] === $object_class && $v["object_id"] === $object_id;
                }
            );
        } else {
            $data = array_filter(
                self::$cache[$module],
                function ($v) {
                    return $v["object_class"] === null && $v["object_id"] === null;
                }
            );
        }

        if ($config_keys) {
            $data = array_filter(
                $data,
                function ($v) use ($config_keys) {
                    return in_array($v["feature"], $config_keys);
                }
            );
        }

        $final_data = [];

        foreach ($data as $_data) {
            $final_data[$_data["feature"]] = $_data["value"];
        }

        return $final_data;
    }

    /**
     * Returns the default values
     *
     * @param string $module      The module of the values to get
     * @param array  $config_keys The keys of the values to get
     *
     * @return array The values
     */
    static public function getDefaultValues($module, $config_keys = null)
    {
        $values = [];

        foreach (self::getConfigsSpecs($module, $config_keys) as $_feature => $_params) {
            $values[$_feature] = $_params["default"];
        }

        return $values;
    }

    /**
     * Get the usable configuration values of an object
     *
     * @param string    $config_inherit The inheritance path
     * @param string    $module         The module to get the configuration of
     * @param array     $config_keys    The keys to get the configuration value of
     * @param CMbObject $object         Object
     *
     * @return array The corresponding configs
     */
    static function getConfigs($config_inherit, $module, $config_keys = null, CMbObject $object = null)
    {
        $ancestor_configs = self::getAncestorsConfigs($config_inherit, $module, $config_keys, $object);

        $configs = [];

        foreach ($ancestor_configs as $_ancestor) {
            $configs = array_merge($configs, $_ancestor["config"]);
        }

        return $configs;
    }

    /**
     * Get all the configs for an inheritance schema, with all the inherited values
     *
     * @param string         $config_inherit Inheritance schema
     * @param string         $module         Module to get config for
     * @param array|null     $config_keys    Configuration keys to get, or null
     * @param CMbObject|null $object         Host object, if none, we'll get global values
     *
     * @return array Configuration values
     */
    static function getAncestorsConfigs($config_inherit, $module, $config_keys = null, CMbObject $object = null)
    {
        $configs = [];

        $parent_config = self::getDefaultValues($module, $config_keys);

        $configs[] = [
            "object"        => "default",
            "config"        => $parent_config,
            "config_parent" => $parent_config,
        ];

        $configs[] = [
            "object"        => "global",
            "config"        => self::getSelfConfig($module, null, null, $config_keys),
            "config_parent" => $parent_config,
        ];

        if ($object) {
            $ancestors            = [];
            $config_inherit_parts = explode(" ", $config_inherit);

            $fwd         = null;
            $prev_object = $object;
            foreach ($config_inherit_parts as $i => $class) {
                $class_fwd = explode(".", $class);

                // Never need the fwd field for the first item
                if ($i === 0) {
                    unset($class_fwd[1]);
                }

                if (count($class_fwd) === 2) {
                    [$class, $fwd] = $class_fwd;
                }

                if ($class === $prev_object->_class && !$fwd) {
                    $ancestors[] = $prev_object;
                } elseif ($fwd) {
                    $object      = $prev_object->loadFwdRef($fwd, true);
                    $ancestors[] = $object;
                    $prev_object = $object;
                }
            }

            $ancestors = array_reverse($ancestors);

            foreach ($ancestors as $_ancestor) {
                $_config = self::getSelfConfig($module, $_ancestor->_class, $_ancestor->_id, $config_keys);

                $configs[] = [
                    "object"        => $_ancestor,
                    "config"        => $_config,
                    "config_parent" => $parent_config,
                ];

                $parent_config = array_merge($parent_config, $_config);
            }
        }

        return $configs;
    }

    /**
     * Change a particular configuration value
     *
     * @param string                      $feature  Feature
     * @param mixed                       $value    Value
     * @param CMbObject|null              $object   Host object
     * @param IConfigurationStrategy|null $strategy Configuration strategy
     * @param bool                        $static   Store as a "static" configuration
     *
     * @return string|null
     * @throws Exception
     */
    static function setConfig(
        $feature,
        $value,
        CMbObject $object = null,
        IConfigurationStrategy $strategy = null,
        bool $static = false
    ) {
        $strategy = new CConfigurationStrategy($strategy);

        return $strategy->setConfig($feature, $value, $object, $static);
    }

    /**
     * Save the configuration values of an object
     *
     * @param array                       $configs  Configs
     * @param CMbObject                   $object   Object
     * @param IConfigurationStrategy|null $strategy Configuration strategy
     * @param bool                        $static   Store as a "static" configuration
     *
     * @return array A list of store messages if any error happens
     * @throws Exception
     */
    static function setConfigs(
        $configs,
        CMbObject $object = null,
        IConfigurationStrategy $strategy = null,
        bool $static = false
    ) {
        $messages = [];

        foreach ($configs as $_feature => $_value) {
            if ($msg = self::setConfig($_feature, $_value, $object, $strategy, $static)) {
                $messages[] = $msg;
            }
        }

        return $messages;
    }

    /**
     * Get self::$values
     *
     * @return array
     */
    static function getValuesConfig()
    {
        return self::$values;
    }

    /**
     * Get self::$hosts
     *
     * @return array
     */
    static function getHostsConfig()
    {
        return self::$hosts;
    }

    /**
     * Update the table_status table to record the last modification date of the configuration for $module
     *
     * @param array|string $module   The module name or an array of modules names
     * @param string       $datetime The last modification datetime
     *
     * @return void
     */
    static function updateTableStatus($module, $datetime)
    {
        $modules = (is_array($module)) ? $module : [$module];

        foreach ($modules as $_module) {
            $table_status       = new CTableStatus();
            $table_status->name = "configuration-{$_module}";

            $table_status->loadMatchingObjectEsc();

            $table_status->update_time = $datetime;
            $table_status->rawStore();
        }
    }

    static function clearForTests()
    {
        self::$values       = [];
        self::$hosts        = [];
        self::$models_infos = [];
        self::$dirty        = [];
        self::$cache        = [];
    }

    static function clearValuesCacheStatus($module)
    {
        $cache = new Cache(self::GET_VALUES_CACHE_STATUS_CACHE, $module, Cache::INNER_OUTER, 60);
        $cache->rem();
    }

    /**
     * Todo: Only get active module ones
     * @return void
     * @throws Exception
     */
    static function registerAllConfiguration()
    {
        $registers = CClassMap::getInstance()->getClassChildren(IConfigurationRegister::class, true, true);

        /** @var IConfigurationRegister $configuration */
        foreach ($registers as $configuration) {
            $configuration->register();
        }

        CConfigurationModelManager::setReady();
    }


    /**
     * @param CStoredObject $object
     *
     * @return void
     * @todo redefine meta raf
     * @deprecated
     */
    public function setObject(CStoredObject $object)
    {
        CMbMetaObjectPolyfill::setObject($this, $object);
    }

    /**
     * @param bool $cache
     *
     * @return bool|CStoredObject|CExObject|null
     * @throws Exception
     * @deprecated
     * @todo redefine meta raf
     */
    public function loadTargetObject($cache = true)
    {
        return CMbMetaObjectPolyfill::loadTargetObject($this, $cache);
    }

    /**
     * @inheritDoc
     * @todo remove
     */
    function loadRefsFwd()
    {
        parent::loadRefsFwd();
        $this->loadTargetObject();
    }
}
