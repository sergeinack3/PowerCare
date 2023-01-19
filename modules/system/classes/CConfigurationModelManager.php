<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CMbObject;
use Ox\Core\CMbObjectSpec;
use Ox\Core\CMbSemaphore;
use Ox\Core\CStoredObject;

/**
 * CConfiguration models manager
 *
 * @todo Handle children where parent is modified (ie. CFunctions-2-group_id=1 to CFunctions-2-group_id=3)
 */
abstract class CConfigurationModelManager implements IShortNameAutoloadable
{
    const TTL_CHECKING_MODEL        = 60;
    const TTL_CHECKING_VALUES       = 60;
    const TTL_LOCK_TIME             = 10;
    const PATTERN_SANITIZED_INHERIT = '@([\w ]+ / )@';

    static private $ready = false;

    /** @var string Current module name */
    static protected $module;

    /** @var array Root nodes */
    static protected $roots = [];

    /** @var array Leaf nodes */
    static protected $leaves = [];

    /** @var array Raw model */
    static protected $raw_model = [];

    /** @var array Inherited raw model */
    static protected $inherited_raw_model = [];

    /** @var array Model */
    static protected $model = [];

    /** @var array Cache of object ids */
    static protected $context_ids = [];

    /** @var array Cache of stored configurations */
    static protected $cache = [];

    /** @var array Cache object storage */
    static protected $storage = [];

    /**
     * Check if configurations are ready
     *
     * @return bool
     */
    public static function isReady()
    {
        return static::$ready;
    }

    /**
     * Mark the configuration manager as ready
     *
     * @return void
     */
    public static function setReady()
    {
        static::$ready = true;
    }

    /**
     * Get the configuration mode according to instance role
     *
     * @return string
     */
    public static function getConfigurationMode()
    {
        switch (CAppUI::conf('instance_role')) {
            case 'prod':
                return 'std';

            default:
                return 'alt';
        }
    }

    /**
     * Get the configuration strategy according to mode
     *
     * @param string|null $mode Configuration mode
     *
     * @return CConfigurationAltStrategy|CConfigurationStdStrategy
     * @throws Exception
     */
    public static function getStrategy($mode = null)
    {
        if (is_null($mode)) {
            $mode = static::getConfigurationMode();
        }

        switch ($mode) {
            case 'std':
                return new CConfigurationStdStrategy();

            case 'alt':
                return new CConfigurationAltStrategy();

            default:
                throw new Exception('Invalid configuration strategy mode');
        }
    }

    /**
     * Set the module of the model to manage
     *
     * @param string $module Module name
     *
     * @return void
     * @throws CMbException
     */
    static protected function setModule($module)
    {
        if (!$module) {
            throw new CMbException('common-error-Missing parameter: %s', 'module');
        }

        static::$module = $module;
    }

    /**
     * Get the current module to manage
     *
     * @return string
     */
    static protected function getModule()
    {
        return static::$module;
    }

    /**
     * Simplify an inheritance schema, removing the prefix
     *
     * @param string $inherit The inheritance schema
     *
     * @return string
     */
    static protected function simplifyInherit($inherit)
    {
        return preg_replace(static::PATTERN_SANITIZED_INHERIT, '', $inherit);
    }

    /**
     * Raw model setter
     *
     * @param array $model Raw model to set
     *
     * @return void
     */
    static public function setRawModel(array $model)
    {
        static::$raw_model = $model;
    }

    /**
     * Get the raw model
     *
     * @return array
     */
    static protected function getRawModel()
    {
        return (isset(static::$raw_model[static::getModule()])) ? static::$raw_model[static::getModule()] : [];
    }

    /**
     * Set inherited raw model
     *
     * @param array $model Inherited raw model
     *
     * @return void
     */
    static protected function setInheritedRawModel(array $model)
    {
        static::$inherited_raw_model[static::getModule()] = $model;
    }

    /**
     * Get the inherited raw model
     *
     * @return array
     */
    static protected function getInheritedRawModel()
    {
        return (isset(
            static::$inherited_raw_model[static::getModule()]
        )) ? static::$inherited_raw_model[static::getModule()] : [];
    }

    /**
     * Get the sorted by inherit raw model
     *
     * @return array
     */
    static protected function getSortedRawModel()
    {
        $raw_model = static::getRawModel();

        uksort(
            $raw_model,
            function ($_a, $_b) {
                $_count_a = substr_count($_a, ' ');
                $_count_b = substr_count($_b, ' ');

                return $_count_a <=> $_count_b;
            }
        );

        return $raw_model;
    }

    /**
     * Register a root node
     *
     * @param CConfigurationModelRoot $root Node to register
     *
     * @return void
     */
    static protected function registerRootNode(CConfigurationModelRoot $root)
    {
        static::$roots[static::getModule()][$root->getContextClass()] = $root;
    }

    /**
     * Register a leaf node
     *
     * @param CConfigurationModelLeaf $leaf Node to register
     *
     * @return void
     */
    static protected function registerLeafNode(CConfigurationModelLeaf $leaf)
    {
        static::$leaves[static::getModule()][$leaf->getInherit()] = $leaf;
    }

    /**
     * Get the root model nodes
     *
     * @return CConfigurationModelRoot[]
     * @throws CMbException
     */
    static protected function getRootNodes()
    {
        if (!isset(static::$roots[static::getModule()]) || !static::$roots[static::getModule()]) {
            // No root nodes, a configuration is mostly asked before CConfiguration::register() (ie. during autoload)
            throw new CMbException('No root nodes in ' . static::getModule());
        }

        return static::$roots[static::getModule()];
    }

    /**
     * Get root node model count
     *
     * @param string $context_class Root node context class
     *
     * @return int
     */
    static protected function countRootNodes($context_class = null)
    {
        if (!$context_class) {
            return (isset(static::$roots[static::getModule()])) ? count(static::$roots[static::getModule()]) : 0;
        }

        return (isset(static::$roots[static::getModule()][$context_class]) && static::$roots[static::getModule(
            )][$context_class]);
    }

    /**
     * Get a root node
     *
     * @param string $context_class Class of the context
     * @param string $inherit       Inherit key
     *
     * @return CConfigurationModelRoot
     */
    static protected function getRootNode($context_class, $inherit)
    {
        if (isset(static::$roots[static::getModule()][$context_class])) {
            return static::$roots[static::getModule()][$context_class];
        }

        return new CConfigurationModelRoot(static::getModule(), $context_class, $inherit);
    }

    /**
     * Get a leaf node
     *
     * @param string $context_class Class of the context
     * @param string $inherit       Inherit key
     *
     * @return CConfigurationModelLeaf
     */
    static protected function getLeafNode($context_class, $inherit)
    {
        if (isset(static::$leaves[static::getModule()][$inherit])) {
            return static::$leaves[static::getModule()][$inherit];
        }

        return new CConfigurationModelLeaf(static::getModule(), $context_class, $inherit);
    }

    /**
     * Prepare the raw mode tree (Root nodes + leaves)
     *
     * @return void
     */
    static protected function registerModelNodes()
    {
        foreach (static::getSortedRawModel() as $_inherit => $_model) {
            $_sanitized_inherit = static::simplifyInherit($_inherit);

            $_classes = explode(' ', $_sanitized_inherit);
            $_count   = count($_classes);

            // Removing module key
            $_simplified_model = [];

            array_walk(
                $_model,
                function ($v) use (&$_simplified_model) {
                    $_simplified_model = $v;
                }
            );

            // Root
            if ($_count === 1) {
                $_model_object = static::getRootNode($_classes[0], $_inherit);

                $_model_object->setModel($_simplified_model);
                $_model_object->setSanitizedInherit($_sanitized_inherit);

                static::registerRootNode($_model_object);
                continue;
            }

            // Leaf
            [$_parent_class, $fwd] = explode('.', $_classes[1]);
            $_model_object = static::getLeafNode($_classes[0], $_inherit);

            $_model_object->setModel($_simplified_model);
            $_model_object->setSanitizedInherit($_sanitized_inherit);
            $_model_object->setFwdField($fwd);

            static::registerLeafNode($_model_object);

            $_parent_node = static::getRootNode($_parent_class, $_parent_class);
            $_parent_node->setSanitizedInherit($_parent_class);
            $_parent_node->addChild($_model_object);

            /**
             * Here, if no root node is present, we are in a situation where the root node is built like LEAF ROOT.FWD_ID
             * So, we register the virtual root node
             */

            if (!static::countRootNodes($_parent_class)) {
                static::registerRootNode($_parent_node);
            }
        }
    }

    /**
     * Build the raw model indexed by inherit keys according to nodes
     *
     * @return void
     * @throws CMbException
     */
    static protected function buildRawModel()
    {
        static::registerModelNodes();

        $model = [];

        foreach (static::getRootNodes() as $_model_object) {
            $model[$_model_object->getInherit()] = $_model_object->getModel();

            /** @var CConfigurationModelLeaf $_child */
            foreach ($_model_object->getChildren() as $_child) {
                $model[$_child->getInherit()] = $_child->getModel();
            }
        }

        static::setInheritedRawModel($model);
    }

    /**
     * Build the model with specifications
     *
     * @return void
     * @throws CMbException
     */
    static protected function buildModel()
    {
        $model_cache = static::getModelCache(static::getModule());

        if ($model_cache->exists()) {
            if (!static::countRootNodes()) {
                // Always register model nodes before consuming model
                static::registerModelNodes();
            }

            static::$model[static::getModule()] = $model_cache->get();

            return;
        }

        $t = microtime(true);

        static::buildRawModel();

        $hash_cache = static::getModelStatusCache(static::getModule());
        $hash_cache->put(md5(serialize(static::getRawModel())));

        $model = [];
        foreach (static::getInheritedRawModel() as $_inherit => $_model) {
            $list = [];

            static::_buildModelLeaf($list, [], $_model);

            if (!isset($model[$_inherit])) {
                $model[$_inherit] = [];
            }

            $model[$_inherit] = array_merge($model[$_inherit], $list);
        }

        $msg = sprintf("'config-model-%s' generated in %f ms", static::getModule(), (microtime(true) - $t) * 1000);
        CApp::log($msg);

        static::$model[static::getModule()] = $model_cache->put($model);
    }

    /**
     * Get the built model
     *
     * @return array
     */
    static protected function getModel()
    {
        return (isset(static::$model[static::getModule()])) ? static::$model[static::getModule()] : [];
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
    static protected function _buildModelLeaf(&$list, $path, $tree)
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
                if (!isset($_spec_options['default'])) {
                    $_spec_options['default'] = '';
                }

                $list[implode(' ', $_path)] = $_spec_options;
            } // ... else a subtree
            else {
                static::_buildModelLeaf($list, $_path, $_subtree);
            }
        }
    }

    /**
     * Load the object ids for each inherit schema
     *
     * @return void
     * @throws CMbException
     */
    static protected function loadContextIDs()
    {
        static::buildModel();

        //$date = CMbDT::dateTime();
        //$contexts = array('global' => $date);

        foreach (static::getRootNodes() as $_root) {
            $_root->loadObjectIDs();

            $_root_ids = $_root->getObjectIDs();

            //foreach ($_root_ids as $_root_id) {
            //  $contexts["{$_root->getContextClass()}-{$_root_id}"] = $date;
            //}

            foreach ($_root->getChildren() as $_child) {
                $_inherit = $_child->getSanitizedInherit();

                if (isset(static::$context_ids[$_inherit])) {
                    $_child->setObjectIDs(static::$context_ids[$_inherit]);
                    continue;
                }

                $_child->loadObjectIDs($_root_ids);
                $_child_ids = $_child->getObjectIDs();

                //foreach ($_child_ids as $_child_id) {
                //  $contexts["{$_child->getContextClass()}-{$_child_id['id']}"] = $date;
                //}

                static::$context_ids[$_inherit] = $_child_ids;
            }
        }

        //$cache = static::getContextInheritanceCache(static::getModule());
        //$cache->put($contexts);
    }

    /**
     * Get the CConfiguration spec
     *
     * @return CMbObjectSpec
     */
    static public function getConfigurationSpec()
    {
        static $spec;

        if (!isset($spec)) {
            $self = new CConfiguration();
            $spec = $self->_spec;
        }

        return $spec;
    }

    /**
     * Get the default model values
     *
     * @param array $keys Keys to get, if provided
     *
     * @return array
     */
    static protected function getDefaultValues($keys = [])
    {
        $values = [];

        // Backward compatibility mode
        if ($keys) {
            $module = static::getModule();

            foreach (static::getModel() as $_inherit => $_models) {
                foreach ($_models as $_key => $_model) {
                    $_module_key = "{$module} {$_key}";

                    // < PHP 5.6
                    if (in_array($_module_key, $keys)) {
                        $values[$_module_key] = $_model['default'];
                    }
                }
            }

            // >= PHP 5.6
            //$values = array_filter(
            //$values,
            //function ($k) use ($keys) {
            //return (in_array($k, $keys));
            //},
            //ARRAY_FILTER_USE_KEY
            //);

            return $values;
        }

        foreach (static::getModel() as $_inherit => $_models) {
            foreach ($_models as $_key => $_model) {
                $values[$_key] = $_model['default'];
            }
        }

        return $values;
    }

    /**
     * Check if model has been modified and clear cache if needed
     *
     * @return void
     * @throws CMbException
     */
    static protected function checkModel()
    {
        $lock = new CMbSemaphore('checking-model-' . static::getModule());
        $lock->acquire(static::TTL_LOCK_TIME, 0.001);

        // Check if model has been modified only every n seconds
        $method_cache = static::getCheckedModelCache(static::getModule());

        if ($method_cache->exists()) {
            $lock->release();

            return;
        }

        $hash_cache = static::getModelStatusCache(static::getModule());

        if (!$hash_cache->exists() || !($hash = $hash_cache->get())) {
            static::clearModelCache(static::getModule());

            // Store in cache that model has been checked AFTER clearing all the configuration module cache
            $method_cache->put(null);

            $lock->release();

            return;
        }

        static::buildRawModel();

        $new_hash = md5(serialize(static::getRawModel()));

        if ($new_hash !== $hash) {
            static::clearModelCache(static::getModule());
        }

        // Store in cache that model has been checked AFTER clearing all the configuration module cache
        $method_cache->put(null);

        $lock->release();

        return;
    }

    /**
     * Check if values have been modified and clear cache if needed
     *
     * @return void
     */
    static protected function checkValues()
    {
        $lock = new CMbSemaphore('checking-values-' . static::getModule());
        $lock->acquire(static::TTL_LOCK_TIME, 0.001);

        // Check if values has been modified only every n seconds
        $cache = static::getCheckedValuesCache(static::getModule());

        if ($cache->exists()) {
            $lock->release();

            return;
        }

        $last_build_cache = static::getConfigurationLastBuildCache(static::getModule());

        if (!$last_build_cache->exists() || !($date = $last_build_cache->get())) {
            static::clearValuesCache(static::getModule());

            $cache->put(null);

            $lock->release();

            return;
        }

        if ((new CTableStatus())->isInstalled()) {
            $status_result = CTableStatus::getInfo(static::getConfigurationSpec()->table, static::getModule());

            if ($status_result['update_time'] > $date) {
                static::clearValuesCache(static::getModule());
            }
        }

        $cache->put(null);

        $lock->release();

        return;
    }

    /**
     * Get the configuration values for a given object, with inheritance
     *
     * @param string $module       Module name
     * @param string $object_class Object class
     * @param int    $object_id    Object ID
     *
     * @return array
     * @throws CMbException
     * @todo Each configuration set in a leaf is deported to other leaves values because of inheritance which doesn't
     *       check inherit keys
     *
     */
    static public function getValues($module, $object_class = null, $object_id = null)
    {
        // Set module for current configuration model
        static::setModule($module);

        // Check if model has been updated and must be regenerated
        static::checkModel();

        // Check if values have been modified and must be regenerated
        static::checkValues();

        /**
         * Here, all caches have been checked and preventively cleared if needed
         */

        $context = ($object_class && $object_id) ? "{$object_class}-{$object_id}" : 'global';

        // Check if given context configuration exists in cache
        $cache = static::getValuesCache(static::getModule(), $context);

        if ($cache->exists()) {
            return $cache->get();
        }

        // Context does not exists in cache, before building context values, we check if given context exists
        if ($context !== 'global') {
            $object = CStoredObject::loadFromGuid($context);

            if (!$object || !$object->_id) {
                throw new CMbModelNotFoundException('common-error-Object %s not found', $context);
            }
        }

        /**
         * Value in cache does not exists:
         *  1. Possible new context
         *  2. OUTER Cache has been cleared checkModel() or checkValues()
         *  3. Another thread is currently building values tree
         */
        $lock = new CMbSemaphore('values-' . static::getModule());
        $lock->acquire(static::TTL_LOCK_TIME, 0.001);

        // Cache may have been already rebuilt if we waited for lock acquisition
        if ($cache->exists()) {
            $msg = sprintf("'config-values-%s-*' have been rebuilt during lock time", static::getModule());
            CApp::log($msg);

            $lock->release();

            return $cache->get();
        }

        // If not, so we build the values tree
        $values = static::buildValues($context);

        // Do not forget to release the lock after building the tree
        $lock->release();

        return $values;
    }

    /**
     * Build the configuration values tree for a given context
     *
     * @param string $context Context ("global" or a GUID)
     *
     * @return array
     * @throws CMbException
     */
    static protected function buildValues($context)
    {
        $t = microtime(true);

        // Load object IDs for all inherits
        static::loadContextIDs();

        // Checking if given context class is allowed in the model
        if (!static::checkInherit($context)) {
            return [];
        }

        // Global + stored default configuration cache
        $global_cache = static::getValuesCache(static::getModule(), 'global');

        if ($global_cache->exists()) {
            $default_values = $global_cache->get();
        } else {
            $default_values = array_merge(static::getDefaultValues(), static::getStoredConfigurations(null, null));
            static::putInStore($global_cache, $default_values, 'global');
        }

        $values = [
            'global' => $default_values,
        ];

        // For each root node...
        foreach (static::getRootNodes() as $_root) {
            // For each root context...
            foreach ($_root->getObjectIDs() as $_id) {
                // Check if context configuration in cache
                $_cache = static::getValuesCache(static::getModule(), "{$_root->getContextClass()}-{$_id}");

                if ($_cache->exists()) {
                    $values["{$_root->getContextClass()}-{$_id}"] = $_cache->get();
                } else {
                    $values["{$_root->getContextClass()}-{$_id}"] =
                        array_merge(
                            $values['global'],
                            static::getStoredConfigurations($_root->getContextClass(), $_id)
                        );

                    static::putInStore(
                        $_cache,
                        $values["{$_root->getContextClass()}-{$_id}"],
                        "{$_root->getContextClass()}-{$_id}"
                    );
                }
            }

            $child_inherits = [];

            // For each child...
            foreach ($_root->getChildren() as $_child) {
                // We do not want to compute two children with same sanitized inherit twice
                if (array_key_exists($_child->getSanitizedInherit(), $child_inherits)) {
                    continue;
                }

                $child_inherits[$_child->getSanitizedInherit()] = null;

                // For each child context...
                foreach ($_child->getObjectIDs() as $_child_id) {
                    // Check if context configuration in cache
                    $_cache = static::getValuesCache(
                        static::getModule(),
                        "{$_child->getContextClass()}-{$_child_id['id']}"
                    );

                    if ($_cache->exists()) {
                        $values["{$_child->getContextClass()}-{$_child_id['id']}"] = $_cache->get();
                    } else {
                        /**
                         * ELSE here because we do not want to regenerate children values if they are in cache
                         * Values in cache are automatically cleared when a configuration is modified
                         */

                        $_child_values = static::getStoredConfigurations($_child->getContextClass(), $_child_id['id']);

                        // No need to recompute values tree because child has no redefined value
                        if (!$_child_values) {
                            static::putInStore(
                                $_cache,
                                $values["{$_root->getContextClass()}-{$_child_id['parent_id']}"],
                                "{$_root->getContextClass()}-{$_child_id['parent_id']}"
                            );
                        } else {
                            $values["{$_child->getContextClass()}-{$_child_id['id']}"] =
                                array_merge(
                                    $values["{$_root->getContextClass()}-{$_child_id['parent_id']}"],
                                    $_child_values
                                );

                            static::putInStore(
                                $_cache,
                                $values["{$_child->getContextClass()}-{$_child_id['id']}"],
                                "{$_child->getContextClass()}-{$_child_id['id']}"
                            );
                        }
                    }
                }
            }
        }

        $msg = sprintf("'config-values-%s-*' generated in %f ms", static::getModule(), (microtime(true) - $t) * 1000);
        CApp::log($msg);

        static::storeValues();

        $last_build_cache = static::getConfigurationLastBuildCache(static::getModule());
        $last_build_cache->put(CMbDT::dateTime());

        return isset($values[$context]) ? CMbArray::unflatten($values[$context]) : [];
    }

    /**
     * Shared storing cache
     *
     * @param Cache  $cache   Cache object
     * @param array  $values  Values to store in cache
     * @param string $context Context of the value
     *
     * @return void
     */
    static protected function putInStore(Cache $cache, &$values, $context)
    {
        if (!isset(static::$storage[$context])) {
            static::$storage[$context] = [
                'caches' => [$cache],
                'values' => $values,
            ];
        } else {
            static::$storage[$context]['caches'][] = $cache;
        }
    }

    /**
     * Store in cache the values from storage
     *
     * @return void
     */
    static protected function storeValues()
    {
        $t = microtime(true);

        /** @var Cache $_cache */
        foreach (static::$storage as $_storage) {
            $_values = CMbArray::unflatten($_storage['values'] ?? []);

            foreach ($_storage['caches'] as $_cache) {
                $_cache->put($_values);
            }
        }

        $msg = sprintf("'config-values-%s-*' written in %f ms", static::getModule(), (microtime(true) - $t) * 1000);
        CApp::log($msg);

        static::$storage = [];
    }

    /**
     * Check if given context is in inheritance schema
     *
     * @param string $context Context ("global" or GUID)
     *
     * @return bool
     * @throws CMbException
     */
    static protected function checkInherit($context)
    {
        if ($context === 'global') {
            return true;
        }

        [$object_class,] = explode('-', $context);

        foreach (static::getRootNodes() as $_root) {
            if ($_root->getContextClass() === $object_class) {
                return true;
            }

            foreach ($_root->getChildren() as $_child) {
                if ($_child->getContextClass() === $object_class) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the configuration values of an object, without inheritance
     *
     * @param string                      $object_class Object class
     * @param integer                     $object_id    Object ID
     * @param array                       $keys         Keys to get, if provided
     * @param IConfigurationStrategy|null $strategy     Configuration strategy
     *
     * @return array The configuration values
     */
    static protected function getStoredConfigurations(
        $object_class = null,
        $object_id = null,
        $keys = [],
        IConfigurationStrategy $strategy = null
    ) {
        if (!isset(static::$cache[static::getModule()]) || !static::$cache[static::getModule()]) {
            $spec   = static::getConfigurationSpec();
            $module = static::getModule();

            $strategy = new CConfigurationStrategy($strategy);

            static::$cache[static::getModule()] = static::getSanitizedStoredConfigurations(
                $strategy,
                $module,
                $spec,
                false
            );
        }

        if ($object_class && $object_id) {
            $data = array_filter(
                static::$cache[static::getModule()],
                function ($v) use ($object_class, $object_id) {
                    return ($v['object_class'] === $object_class && $v['object_id'] === $object_id && $v['static'] === '0');
                }
            );
        } else {
            $data = array_filter(
                static::$cache[static::getModule()],
                function ($v) {
                    return ($v['object_class'] === null && $v['object_id'] === null && $v['static'] === '0');
                }
            );
        }

        $final_data = [];

        // Backward compatibility mode
        if ($keys) {
            $module = static::getModule();

            foreach ($data as $_data) {
                $_module_key = "{$module} {$_data['feature']}";

                // < PHP 5.6
                if (in_array($_module_key, $keys)) {
                    $final_data[$_module_key] = $_data['value'];
                }
            }

            // >= PHP 5.6
            //$final_data = array_filter(
            //  $final_data,
            //  function ($k) use ($keys) {
            //    return (in_array($k, $keys));
            //  },
            //  ARRAY_FILTER_USE_KEY
            //);

            return $final_data;
        }

        foreach ($data as $_data) {
            $final_data[$_data['feature']] = $_data['value'];
        }

        return $final_data;
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
    private static function getSanitizedStoredConfigurations(
        CConfigurationStrategy $strategy,
        string                 $module,
        CMbObjectSpec          $spec,
        bool                   $static = false
    ): array {
        $stored_configurations = $strategy->getStoredConfigurations($module, $spec, $static);

        // Using the default values as model for feature integrity
        $model = static::getDefaultValues();

        return array_filter(
            $stored_configurations,
            function (array $configuration) use ($model) {
                return isset($model[$configuration['feature']]);
            }
        );
    }

    /**
     * Clear all configuration cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     */
    static public function clearCache($module)
    {
        static::clearModelCache($module);

        if (isset(static::$cache[$module])) {
            unset(static::$cache[$module]);
        }
    }

    /**
     * Clear the configuration model cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     */
    static protected function clearModelCache($module)
    {
        $cache = static::getModelCache($module);
        $cache->rem();

        static::clearCheckedModelCache($module);
        static::clearModelStatusCache($module);

        // If model cleared, no need to keep values
        static::clearValuesCache($module);
    }

    /**
     * Clear configuration values cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     * @todo Use Cache abstraction instead of SHM
     *
     */
    static public function clearValuesCache($module)
    {
        static::clearCheckedValuesCache($module);
        static::clearConfigurationLastBuildCache($module);

        Cache::deleteKeys(Cache::OUTER, "config-values-{$module}-");
    }

    /**
     * Clear the configuration status model cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     */
    static protected function clearModelStatusCache($module)
    {
        $cache = static::getModelStatusCache($module);
        $cache->rem();
    }

    /**
     * Clear the configuration model checker cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     */
    static protected function clearCheckedModelCache($module)
    {
        $cache = static::getCheckedModelCache($module);
        $cache->rem();
    }

    /**
     * Clear the configuration values checker cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     */
    static protected function clearCheckedValuesCache($module)
    {
        $cache = static::getCheckedValuesCache($module);
        $cache->rem();
    }

    /**
     * Clear the configuration update cache for a given module
     *
     * @param string $module Module name
     *
     * @return void
     */
    static protected function clearConfigurationLastBuildCache($module)
    {
        $cache = static::getConfigurationLastBuildCache($module);
        $cache->rem();
    }

    /**
     * Get configuration values cache for given module and context
     *
     * @param string $module  Module name
     * @param string $context Context key
     *
     * @return Cache
     */
    static protected function getValuesCache($module, $context)
    {
        return new Cache("config-values-{$module}", $context, Cache::INNER_OUTER);
    }

    /**
     * Get the configuration model cache for a given module
     *
     * @param string $module Module name
     *
     * @return Cache
     */
    static protected function getModelCache($module)
    {
        return new Cache('config-model', $module, Cache::INNER_OUTER);
    }

    /**
     * Get the configuration model status cache for a given module
     *
     * @param string $module Module name
     *
     * @return Cache
     */
    static protected function getModelStatusCache($module)
    {
        return new Cache('config-model-hash', $module, Cache::INNER_OUTER);
    }

    /**
     * Check if the configuration has been recently checked in cache for a given module
     *
     * @param string $module Module name
     *
     * @return Cache
     */
    static protected function getCheckedModelCache($module)
    {
        return new Cache('config-checked-model', $module, Cache::INNER_OUTER, static::TTL_CHECKING_MODEL);
    }

    /**
     * Check if we need to check the table status date in DB for a given module configuration
     *
     * @param string $module Module name
     *
     * @return Cache
     */
    static protected function getCheckedValuesCache($module)
    {
        return new Cache('config-checked-values', $module, Cache::INNER_OUTER, static::TTL_CHECKING_VALUES);
    }

    /**
     * Get the configuration cache last generation date for a given module
     *
     * @param string $module Module name
     *
     * @return Cache
     */
    static protected function getConfigurationLastBuildCache($module)
    {
        return new Cache('config-last-build', $module, Cache::INNER_OUTER);
    }

    /**
     * Get the specs of a configuration
     *
     * @param string $feature Space separated feature name
     *
     * @return null|array
     * @throws CMbException
     */
    static public function getConfigSpec($feature)
    {
        [$module, $_feature] = explode(' ', $feature, 2);

        static::setModule($module);
        static::buildModel();

        foreach (static::getModel() as $_inherit => $_model) {
            if (array_key_exists($_feature, $_model)) {
                return $_model[$_feature];
            }
        }

        return null;
    }

    /**
     * Get model within backward compatibility mode
     *
     * @param string $module   Module name
     * @param array  $inherits Inherits
     *
     * @return array
     * @throws CMbException
     */
    static public function _getModel($module, $inherits = [])
    {
        static::setModule($module);

        try {
            static::buildModel();
            $model = static::getModel();
        } catch (CMbException $e) {
            $model = [];
        }

        if ($inherits) {
            $inherits = (is_array($inherits)) ? $inherits : [$inherits];
        }

        $_final_model = [];

        // < PHP 5.6
        foreach ($model as $_inherit => &$_model) {
            if ($_model && (!$inherits || ($inherits && in_array($_inherit, $inherits)))) {
                $_final_model[$_inherit] = $_model;
            }
        }

        // >= PHP 5.6
        // Removing abstract root nodes
        //$model = array_filter(
        //  $model,
        //  function ($_model, $_inherit) use ($inherits) {
        //    return ($_model && (!$inherits || ($inherits && in_array($_inherit, $inherits))));
        //  },
        //  ARRAY_FILTER_USE_BOTH
        //);

        $final_model = [];

        // < PHP 7.0
        foreach ($_final_model as $_inherit => &$_model) {
            $final_model[$_inherit] = [];

            foreach ($_model as $_feature => $_value) {
                $final_model[$_inherit]["{$module} {$_feature}"] = $_value;
            }
        }

        // >= PHP 7.0
        // Adding extra module to feature for backward compatibility
        //foreach ($model as $_inherit => &$_model) {
        //  foreach ($_model as $_feature => $_value) {
        //    $_model["{$module} {$_feature}"] = $_value;
        //    unset($_model[$_feature]);
        //  }
        //}

        return $final_model;
    }

    /**
     * Get all the configs for an inheritance schema, with all the inherited values
     *
     * @param string                      $config_inherit Inheritance schema
     * @param string                      $module         Module to get config for
     * @param array|null                  $config_keys    Configuration keys to get, or null
     * @param CStoredObject|null          $object         Host object, if none, we'll get global values
     * @param IConfigurationStrategy|null $strategy       Configuration strategy
     *
     * @return array Configuration values
     * @throws CMbException
     */
    static public function getAncestorsConfigs(
        $config_inherit,
        $module,
        $config_keys = null,
        CStoredObject $object = null,
        IConfigurationStrategy $strategy = null
    ) {
        static::setModule($module);
        static::buildModel();

        $configs       = [];
        $parent_config = static::getDefaultValues($config_keys);

        $configs[] = [
            'object'        => 'default',
            'config'        => $parent_config,
            'config_parent' => $parent_config,
        ];

        $configs[] = [
            'object'        => 'global',
            'config'        => static::getStoredConfigurations(null, null, $config_keys, $strategy),
            'config_parent' => $parent_config,
        ];

        if ($object) {
            $ancestors            = [];
            $config_inherit_parts = explode(' ', $config_inherit);

            $fwd         = null;
            $prev_object = $object;
            foreach ($config_inherit_parts as $i => $class) {
                $class_fwd = explode('.', $class);

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
                $_config = static::getStoredConfigurations(
                    $_ancestor->_class,
                    $_ancestor->_id,
                    $config_keys,
                    $strategy
                );

                $configs[] = [
                    'object'        => $_ancestor,
                    'config'        => $_config,
                    'config_parent' => $parent_config,
                ];

                $parent_config = array_merge($parent_config, $_config);
            }
        }

        return $configs;
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
     * @throws CMbException
     */
    static public function getConfigs($config_inherit, $module, $config_keys = null, CMbObject $object = null)
    {
        $ancestor_configs = static::getAncestorsConfigs($config_inherit, $module, $config_keys, $object);

        $configs = [];

        foreach ($ancestor_configs as $_ancestor) {
            $configs = array_merge($configs, $_ancestor['config']);
        }

        return $configs;
    }

    /**
     * Get module configurations
     *
     * @param string $module  The module
     * @param string $inherit The inherit schema
     *
     * @return array
     * @throws CMbException
     */
    static public function getModuleConfigs($module, $inherit = null)
    {
        $model = static::_getModel($module, $inherit);

        $configs      = [];
        $module_start = "{$module} ";

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
     * Get class configs
     *
     * @param string $class   Class name
     * @param string $module  Module
     * @param string $inherit Inheritance schema
     * @param bool   $flatten Flatten output
     *
     * @return array
     * @throws CMbException
     */
    static public function getClassConfigs($class, $module, $inherit = null, $flatten = true)
    {
        $configs = [];

        $model = static::getModuleConfigs($module, $inherit);

        $patterns = ["{$class} ", "{$class}."];

        foreach ($model as $_inherit => $_configs) {
            foreach ($patterns as $_patt) {
                $_inherit = static::simplifyInherit($_inherit);

                // Faster than preg_match
                if ($_inherit === $class || strpos($_inherit, $_patt) !== false) {
                    if ($flatten) {
                        $configs = array_merge($configs, $_configs);
                    } else {
                        $configs[$_inherit] = $_configs;
                    }
                    break;
                }
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
    static public function getObjectTree($inherit)
    {
        return CConfiguration::getObjectTree($inherit);
    }

    /**
     * Get model cache status
     *
     * @param string $module Name of the module to get model for
     *
     * @return string Can be CConfiguration::STATUS_EMPTY, CConfiguration::STATUS_DIRTY or CConfiguration::STATUS_OK
     * @throws CMbException
     */
    static public function getModelCacheStatus($module)
    {
        $hash_cache = static::getModelStatusCache($module);

        if (!$hash_cache->exists() || !($hash = $hash_cache->get())) {
            return CConfiguration::STATUS_EMPTY;
        }

        static::setModule($module);
        static::buildRawModel();

        $new_hash = md5(serialize(static::getRawModel()));

        if ($new_hash !== $hash) {
            return CConfiguration::STATUS_DIRTY;
        }

        return CConfiguration::STATUS_OK;
    }

    /**
     * Get the values cache status
     *
     * @param string $module Name of the module the cache status will be return for
     *
     * @return string The values can be CConfiguration::STATUS_EMPTY, CConfiguration::STATUS_DIRTY or
     *                CConfiguration::STATUS_OK
     */
    static public function getValuesCacheStatus($module)
    {
        $last_build_cache = static::getConfigurationLastBuildCache($module);

        if (!$last_build_cache->exists() || !($date = $last_build_cache->get())) {
            return CConfiguration::STATUS_EMPTY;
        }

        if ((new CTableStatus())->isInstalled()) {
            $status_result = CTableStatus::getInfo(static::getConfigurationSpec()->table, $module);

            if ($status_result['update_time'] > $date) {
                return CConfiguration::STATUS_DIRTY;
            }
        }

        return CConfiguration::STATUS_OK;
    }

    ///**
    // * Get the context inherited list in cache
    // *
    // * @param string $module Module name
    // *
    // * @todo Use this to handle configuration regeneration by context (not compatible with distributed servers yet)
    // *
    // * @return Cache
    // */
    //static protected function getContextInheritanceCache($module) {
    //  return new Cache('config-context', $module, Cache::OUTER);
    //}
}
