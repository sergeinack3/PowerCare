<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CClassMap;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbSemaphore;
use Ox\Core\Module\ModuleManagerTrait;

/**
 * "Static" configuration manager
 *
 * Will be refactored in order to handle all kind of CConfiguration and allow the suppression of
 * CConfigurationModelManager class
 *
 * Todo: Inject LayeredCache dependency when available
 * Todo: Inject Logger dependency
 * Todo: Refactor and merge with CConfigurationModelManager
 */
final class ConfigurationManager
{
    use ModuleManagerTrait;

    /** @var int TTL in seconds of model checked cache entry */
    private const TTL_CHECKING_MODEL = 60;

    /** @var int TTL in seconds of values checked cache entry */
    private const TTL_CHECKING_VALUES = 60;

    /** @var int Maximal duration in seconds of locking during checking and building */
    private const TTL_LOCK_TIME = 10;

    /** @var self */
    private static $instance;

    /** @var bool */
    private $ready = false;

    /** @var array Temporary raw model without module index */
    private $temporary_raw_model = [];

    /** @var array The raw model (indexed by module) */
    private $raw_model = [];

    /** @var array The specification model (indexed by module) */
    private $model = [];

    /** @var array Private storage for serialization optimization */
    private $storage = [];

    /** @var array Stored configurations cache */
    private $cache = [];

    /**
     * ConfigurationManager singleton
     *
     * @return self
     * @throws CMbException
     * @throws ConfigurationException
     */
    public static function get(): self
    {
        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $instance = new self();
        $instance->registerAll();

        return self::$instance = $instance;
    }

    /**
     * Register all the module's configurations
     *
     * @return void
     * @throws CMbException
     * @throws ConfigurationException
     */
    private function registerAll(): void
    {
        $registers = CClassMap::getInstance()->getClassChildren(IConfigurationRegister::class, true, true);

        if (!$registers) {
            return;
        }

        /** @var IConfigurationRegister $_module_register */
        foreach ($registers as $_module_register) {
            $_module_name = $this->getModuleForClass(get_class($_module_register));

            if (!$this->isModuleActive($_module_name)) {
                continue;
            }

            $_module_register->registerStatic($this);
            $this->applyForModule($_module_name);
        }

        $this->setReady(true);
    }

    /**
     * Register a raw model for a given module
     *
     * Todo: Check if a key of a static configuration is not the same as a non-static one
     *
     * @param string $module
     *
     * @return void
     * @throws ConfigurationException
     */
    private function applyForModule(string $module): void
    {
        // Module does not declare static configuration
        if (empty($this->temporary_raw_model)) {
            return;
        }

        if (array_key_exists($module, $this->raw_model)) {
            throw ConfigurationException::moduleAlreadyHasRegisteredStaticConfigurations($module);
        }

        $this->raw_model[$module]  = $this->temporary_raw_model;
        $this->temporary_raw_model = [];
    }

    /**
     * Tell whether the manager is ready
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->ready;
    }

    /**
     * Set the ready state
     *
     * @param bool $ready
     *
     * @return void
     */
    private function setReady(bool $ready): void
    {
        $this->ready = $ready;
    }

    /**
     * Get a configuration value
     *
     * @param string $key The configuration key ("<module><whitespace>(<token><whitespace>?)+")
     *
     * @return mixed|null
     * @throws ConfigurationException
     * @throws ConfigurationNotReadyException
     */
    public function getValue(string $key)
    {
        if (!$this->isReady()) {
            throw new ConfigurationNotReadyException(
                'ConfigurationManager-error-The configuration manager is not ready'
            );
        }

        // Cache for massively requested keys
        // /!\ SHOULD only be INNER
        $cache = new Cache('config-static-value', $key, Cache::INNER);
        $value = $cache->get();

        if ($value !== null) {
            return $value;
        }

        // Key is not in "<module><whitespace>(<token><whitespace>?)+" format
        if (strpos($key, ' ') === false) {
            throw ConfigurationException::invalidParameter($key);
        }

        [$module, $key] = explode(' ', $key, 2);

        try {
            $values = $this->getValues($module);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);

            return null;
        }

        return $cache->put(($values) ? CMbArray::readFromPath($values, $key) : '');
    }

    public function getValuesForModule(string $mod_name): array
    {
        if (!$this->isReady()) {
            throw new ConfigurationNotReadyException(
                'ConfigurationManager-error-The configuration manager is not ready'
            );
        }

        return $this->getValues($mod_name);
    }

    /**
     * Log a caching information
     *
     * @param string $msg
     * @param mixed  ...$args
     *
     * @return void
     * @throws Exception
     */
    private function log(string $msg, ...$args): void
    {
        CApp::log(sprintf($msg, ...$args));
    }

    private function getValues(string $module): array
    {
        // Check if model has been updated and must be regenerated
        $this->checkModel($module);

        // Check if values have been modified and must be regenerated
        $this->checkValues($module);

        /**
         * Here, all caches have been checked and preventively cleared if needed
         */

        // Check if configurations exist in cache
        $cache  = $this->getValuesCache($module);
        $values = $cache->get();

        if ($values !== null) {
            return $values;
        }

        /**
         * Values in cache do not exist:
         *  1. OUTER cache has been cleared checkModel() or checkValues()
         *  2. Another thread is currently building the values tree
         */
        $lock = new CMbSemaphore('static-values');
        $lock->acquire(self::TTL_LOCK_TIME, 0.001);

        // Cache may have been already rebuilt if we waited for lock acquisition
        $values = $cache->get();
        if ($values !== null) {
            $this->log("'config-static-values-%s' have been rebuilt during lock time", $module);

            $lock->release();

            return $values;
        }

        // If not, so we build the values tree
        $values = $this->buildValues($module);

        // Do not forget to release the lock after building the tree
        $lock->release();

        return $values;
    }

    /**
     * Check if the model cache must be regenerated
     *
     * @param string $module
     *
     * @return void
     * @throws ConfigurationException
     */
    private function checkModel(string $module): void
    {
        $lock = new CMbSemaphore("checking-static-model-{$module}");
        $lock->acquire(self::TTL_LOCK_TIME, 0.001);

        // Check if model has been modified only every n seconds
        $method_cache = $this->getCheckedModelCache($module);

        if ($method_cache->exists()) {
            $lock->release();

            return;
        }

        $hash_cache = $this->getModelStatusCache($module);
        $hash       = $hash_cache->get();

        if ($hash === null) {
            $this->clearModelCache($module);

            // Store in cache that model has been checked AFTER clearing all the configuration module cache
            $method_cache->put(null);

            $lock->release();

            return;
        }

        $new_hash = $this->computeRawModelHash($module);

        if ($new_hash !== $hash) {
            $this->clearModelCache($module);
        }

        // Store in cache that model has been checked AFTER clearing all the configuration module cache
        $method_cache->put(null);

        $lock->release();
    }

    /**
     * Check if values cache must be regenerated
     *
     * @param string $module
     *
     * @return void
     */
    private function checkValues(string $module): void
    {
        $lock = new CMbSemaphore('checking-static-values');
        $lock->acquire(self::TTL_LOCK_TIME, 0.001);

        // Check if values has been modified only every n seconds
        $cache = $this->getCheckedValuesCache($module);

        if ($cache->exists()) {
            $lock->release();

            return;
        }

        $last_build_cache = $this->getConfigurationLastBuildCache($module);
        $date             = $last_build_cache->get();

        if ($date === null) {
            $this->clearValuesCache($module);

            $cache->put(null);

            $lock->release();

            return;
        }

        if ((new CTableStatus())->isInstalled()) {
            $status_result = CTableStatus::getInfo($this->getConfigurationSpec()->table, $module);

            if ($status_result['update_time'] > $date) {
                $this->clearValuesCache($module);
            }
        }

        $cache->put(null);

        $lock->release();

        return;
    }

    /**
     * Get the CConfiguration spec
     *
     * @return CMbObjectSpec
     */
    public function getConfigurationSpec(): CMbObjectSpec
    {
        static $spec;

        if (!isset($spec)) {
            $self = new CConfiguration();
            $spec = $self->_spec;
        }

        return $spec;
    }

    /**
     * Check if the configuration has been recently checked in cache for a given module
     *
     * @param string $module
     *
     * @return Cache
     */
    private function getCheckedModelCache(string $module): Cache
    {
        return new Cache('config-static-checked-model', $module, Cache::INNER_OUTER, self::TTL_CHECKING_MODEL);
    }

    /**
     * Get the configuration model status cache for a given module
     *
     * @param string $module
     *
     * @return Cache
     */
    private function getModelStatusCache(string $module): Cache
    {
        return new Cache('config-static-model-hash', $module, Cache::INNER_OUTER);
    }

    /**
     * Get the configuration model cache for a given module
     *
     * @param string $module
     *
     * @return Cache
     */
    private function getModelCache(string $module): Cache
    {
        return new Cache('config-static-model', $module, Cache::INNER_OUTER);
    }

    /**
     * Clear the configuration model cache for a given module
     *
     * @param string $module
     *
     * @return void
     */
    private function clearModelCache(string $module): void
    {
        $cache = $this->getModelCache($module);
        $cache->rem();

        $this->clearCheckedModelCache($module);
        $this->clearModelStatusCache($module);

        // If model cleared, no need to keep values
        $this->clearValuesCache($module);
    }

    /**
     * Clear the configuration status model cache for a given module
     *
     * @param string $module
     *
     * @return void
     */
    private function clearModelStatusCache(string $module): void
    {
        $cache = $this->getModelStatusCache($module);
        $cache->rem();
    }

    /**
     * Clear the configuration model checker cache for a given module
     *
     * @param string $module
     *
     * @return void
     */
    private function clearCheckedModelCache(string $module): void
    {
        $cache = $this->getCheckedModelCache($module);
        $cache->rem();
    }

    /**
     * Clear configuration values cache for a given module
     *
     * @param string $module
     *
     * @return void
     */
    public function clearValuesCache(string $module): void
    {
        $this->clearCheckedValuesCache($module);
        $this->clearConfigurationLastBuildCache($module);

        $values_cache = $this->getValuesCache($module);
        $values_cache->rem();
    }

    /**
     * Get the configuration values cache for a given module
     *
     * @param string $module
     *
     * @return Cache
     */
    private function getValuesCache(string $module): Cache
    {
        return new Cache('config-static-values', $module, Cache::INNER_OUTER);
    }

    /**
     * Clear the configuration values checker cache for a given module
     *
     * @param string $module
     *
     * @return void
     */
    private function clearCheckedValuesCache(string $module): void
    {
        $cache = $this->getCheckedValuesCache($module);
        $cache->rem();
    }

    /**
     * Clear the configuration update cache for a given module
     *
     * @param string $module
     *
     * @return void
     */
    private function clearConfigurationLastBuildCache(string $module): void
    {
        $cache = $this->getConfigurationLastBuildCache($module);
        $cache->rem();
    }

    /**
     * Check if we need to check the table status date in DB for a given module configuration
     *
     * @param string $module
     *
     * @return Cache
     */
    private function getCheckedValuesCache(string $module): Cache
    {
        return new Cache('config-static-checked-values', $module, Cache::INNER_OUTER, self::TTL_CHECKING_VALUES);
    }

    /**
     * @param string $module
     *
     * @return Cache
     */
    private function getConfigurationLastBuildCache(string $module): Cache
    {
        return new Cache('config-static-last-build', $module, Cache::INNER_OUTER);
    }

    /**
     * Register a static configuration
     *
     * @param array $configurations
     *
     * @return void
     */
    public function registerStatic(array $configurations): void
    {
        $this->temporary_raw_model = $configurations;
    }

    /**
     * Get the configuration raw model (without built specifications) of a module
     *
     * @param string $module
     *
     * @return array
     * @throws ConfigurationException
     */
    private function getRawModel(string $module): array
    {
        if (!array_key_exists($module, $this->raw_model)) {
            throw ConfigurationException::moduleDoesNotHaveStaticConfiguration($module);
        }

        return $this->raw_model[$module];
    }

    /**
     * Get the specs of a configuration.
     *
     * @param string $feature Space separated feature name.
     *
     * @return array|null
     * @throws CMbException
     * @throws ConfigurationException
     */
    public static function getConfigSpec(string $feature): ?array
    {
        [$module, $_feature] = explode(' ', $feature, 2);

        return (self::get()->model[$module][$_feature]) ?? null;
    }

    /**
     * Build the model with specifications
     *
     * @param string $module
     *
     * @return array
     * @throws ConfigurationException
     */
    public function buildModel(string $module): array
    {
        $model_cache = $this->getModelCache($module);
        $model       = $model_cache->get();

        if ($model !== null) {
            return $this->model[$module] = $model;
        }

        // Model building
        $t = microtime(true);

        $list = [];
        $this->buildModelPart($list, [], $this->getRawModel($module));

        $this->model[$module] = $list;

        $hash_cache = $this->getModelStatusCache($module);
        $hash_cache->put($this->computeRawModelHash($module));

        $this->log("'config-static-model-%s' generated in %f ms", $module, (microtime(true) - $t) * 1000);

        return $model_cache->put($this->model[$module]);
    }

    /**
     * Compute a module's raw model hash
     *
     * @param string $module
     *
     * @return string
     * @throws ConfigurationException
     */
    private function computeRawModelHash(string $module): string
    {
        return md5(serialize($this->getRawModel($module)));
    }

    /**
     * Build a model leaf
     *
     * @param array $list Global tree
     * @param array $path Path keys
     * @param array $tree Sub tree
     *
     * @return void
     */
    private function buildModelPart(&$list, $path, $tree)
    {
        foreach ($tree as $_key => $_subtree) {
            $_path   = $path;
            $_path[] = $_key;

            // If a leaf (prop)
            if (is_string($_subtree)) {
                // Build spec
                $_parts = explode(' ', $_subtree);

                $_spec_options = [
                    'type'   => array_shift($_parts),
                    'string' => $_subtree,
                ];

                foreach ($_parts as $_part) {
                    $_options                              = explode('|', $_part, 2);
                    $_spec_options[array_shift($_options)] = count($_options) ? $_options[0] : true;
                }

                // Always have a default value
                $_spec_options['default'] = ($_spec_options['default']) ?? '';

                $list[implode(' ', $_path)] = $_spec_options;
            } else {
                // ... else a subtree
                $this->buildModelPart($list, $_path, $_subtree);
            }
        }
    }

    /**
     * Build the configuration values tree for a given context
     *
     * @param string $module
     *
     * @return array
     * @throws ConfigurationException
     */
    private function buildValues(string $module): array
    {
        $t = microtime(true);

        $this->buildModel($module);

        // Global + stored default configuration cache
        $global_cache = $this->getValuesCache($module);

        $default_values = $global_cache->get();

        if ($default_values === null) {
            $default_values = array_merge(
                $this->getDefaultValues($module, []),
                $this->getStoredConfigurations($module, [], null)
            );
            $this->putInCacheStore($module, $global_cache, $default_values);
        }

        $this->log("'config-static-values-%s' generated in %f ms", $module, (microtime(true) - $t) * 1000);

        $this->cacheValues($module);

        $last_build_cache = $this->getConfigurationLastBuildCache($module);
        $last_build_cache->put(CMbDT::dateTime());

        return CMbArray::unflatten($default_values);
    }

    /**
     * Get the default model values
     *
     * @param string $module The module
     * @param array  $keys   Keys to get, if provided
     *
     * @return array
     */
    private function getDefaultValues(string $module, array $keys = []): array
    {
        $values = [];

        if (empty($keys)) {
            foreach ($this->model[$module] as $_key => $_model) {
                $values[$_key] = $_model['default'];
            }

            return $values;
        }

        // If provided, config keys are prefixed by module
        array_walk(
            $keys,
            function (&$k) {
                $k = explode(' ', $k, 2)[1];
            }
        );

        $filtered_keys = array_filter(
            $this->model[$module],
            function ($k) use ($keys) {
                return in_array($k, $keys);
            },
            ARRAY_FILTER_USE_KEY
        );

        foreach ($filtered_keys as $_key => $_model) {
            $values["{$module} {$_key}"] = $_model['default'];
        }

        return $values;
    }

    /**
     * Get the stored configuration values
     *
     * @param string                      $module
     * @param array                       $keys     Keys to get, if provided
     * @param IConfigurationStrategy|null $strategy Configuration strategy
     *
     * @return array
     * @throws Exception
     */
    private function getStoredConfigurations(string $module, array $keys = [], IConfigurationStrategy $strategy = null)
    {
        $spec     = static::getConfigurationSpec();
        $strategy = new CConfigurationStrategy($strategy);

        $configurations = $this->getSanitizedStoredConfigurations($strategy, $module, $spec, true);

        $data = array_filter(
            $configurations,
            function ($v) {
                return ($v['object_class'] === null && $v['object_id'] === null && $v['static'] === '1');
            }
        );

        $filtered_data = [];

        if (empty($keys)) {
            foreach ($data as $_data) {
                $filtered_data[$_data['feature']] = $_data['value'];
            }

            return $filtered_data;
        }

        // If provided, config keys are prefixed by module
        foreach ($data as $_data) {
            $_module_key = "{$module} {$_data['feature']}";

            if (in_array($_module_key, $keys)) {
                $filtered_data[$_module_key] = $_data['value'];
            }
        }

        return $filtered_data;
    }

    /**
     * Get the filtered stored features (with feature name integrity check).
     *
     * @param CConfigurationStrategy $strategy
     * @param string                 $module
     * @param CMbObjectSpec          $spec
     * @param bool                   $static
     *
     * @return array
     */
    private function getSanitizedStoredConfigurations(
        CConfigurationStrategy $strategy,
        string $module,
        CMbObjectSpec $spec,
        bool $static = false
    ): array {
        $stored_configurations = $strategy->getStoredConfigurations($module, $spec, $static);

        // Using the default values as model for feature integrity
        $model = $this->getDefaultValues($module);

        return array_filter(
            $stored_configurations,
            function (array $configuration) use ($model) {
                return isset($model[$configuration['feature']]);
            }
        );
    }

    /**
     * Get all the configs for an inheritance schema, with all the inherited values
     *
     * @param string                      $module      Module to get configurations for
     * @param array                       $config_keys Configuration keys to get, or empty
     * @param IConfigurationStrategy|null $strategy    Configuration strategy
     *
     * @return array
     * @throws Exception
     */
    public function getAncestorsConfigs(
        string $module,
        array $config_keys = [],
        IConfigurationStrategy $strategy = null
    ): array {
        // If provided, config_keys are prefixed by module
        $configs       = [];
        $parent_config = $this->getDefaultValues($module, $config_keys);

        $configs[] = [
            'object'        => 'default',
            'config'        => $parent_config,
            'config_parent' => $parent_config,
        ];

        $configs[] = [
            'object'        => 'global',
            'config'        => $this->getStoredConfigurations($module, $config_keys, $strategy),
            'config_parent' => $parent_config,
        ];

        return $configs;
    }

    /**
     * Shared storing cache
     *
     * @param string $module
     * @param Cache  $cache  Cache object
     * @param array  $values Values to store in cache
     *
     * @return void
     */
    private function putInCacheStore(string $module, Cache $cache, array &$values): void
    {
        if (!array_key_exists($module, $this->storage)) {
            $this->storage[$module] = [
                'caches' => [$cache],
                'values' => $values,
            ];
        } else {
            $this->storage[$module]['caches'][] = $cache;
        }
    }

    /**
     * Store in cache the values from storage
     *
     * @param string $module
     *
     * @return void
     * @throws Exception
     */
    private function cacheValues(string $module): void
    {
        $t = microtime(true);

        $values = CMbArray::unflatten($this->storage[$module]['values']);

        foreach ($this->storage[$module]['caches'] as $_cache) {
            $_cache->put($values);
        }

        $this->log("'config-static-values-%s' cached in %f ms", $module, (microtime(true) - $t) * 1000);

        $this->storage[$module] = [];
    }

    /**
     * Clear all configuration cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     */
    public function clearCache(string $module): void
    {
        $this->clearModelCache($module);
    }
}
