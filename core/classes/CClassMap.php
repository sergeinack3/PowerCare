<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Exception;
use Ox\Core\Autoload\CAutoloadAlias;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\FieldSpecs\CEnumSpec;
use Ox\Core\FieldSpecs\CRefChecker;
use Ox\Core\FieldSpecs\CRefCheckerException;
use Ox\Core\FieldSpecs\CRefSpec;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Controllers\Legacy\CMainController;
use Ox\Mediboard\System\Forms\CExObject;
use ReflectionClass;
use ReflectionException;
use stdClass;
use Throwable;

/**
 *  Class map manager (singleton)
 */
final class CClassMap
{
    private const DEFINITION_REF = 'ref';
    private const DEFINITION_MAP = 'map';
    private const EXTENSION_PHP  = '.php';

    /**
     * @var CClassMap $instance
     */
    private static $instance = null;

    private $classmap           = [];
    private $classref           = [];
    private $legacy_actions     = [];
    private $alias              = [];
    private $modules            = [];
    private $modules_namespaces = [];
    private $root_dir;
    private $file_classmap;
    private $file_classref;
    private $file_legacy_actions;
    private $excluded_dirs      = [];
    private $dirs               = [
        'core/classes',
        'modules/*/classes',
    ];

    /**
     * @return CClassMap
     * @throws Exception
     */
    public static function getInstance(): CClassMap
    {
        if (self::$instance === null) {
            self::$instance = new CClassMap();
        }

        return self::$instance;
    }


    /**
     * CClassMap constructor
     *
     * @throws Exception
     */
    private function __construct()
    {
        // Init
        $this->root_dir            = dirname(__DIR__, 2);
        $this->file_classmap       = $this->root_dir . '/includes/classmap.php';
        $this->file_classref       = $this->root_dir . '/includes/classref.php';
        $this->file_legacy_actions = $this->root_dir . '/includes/legacy_actions.php';

        // Set class map
        if (file_exists($this->file_classmap)) {
            $this->classmap = include $this->file_classmap;

            // Static cache class alias
            $this->buildClassAlias();

            // Static cache modules
            $this->buildModules();
        }

        // Set class ref
        if (file_exists($this->file_classref)) {
            $this->classref = include $this->file_classref;
        }
    }

    /**
     * Force instance initialisation
     *
     * @return void
     * @throws Exception
     */
    public static function init(): void
    {
        static::getInstance();
    }


    /**
     * Build class map (by composer)
     *
     * @return string
     * @throws Exception
     */
    public function buildClassMap(): string
    {
        // Start
        $time_start = microtime(true);

        // Init
        $this->classmap      = [];
        $sep                 = DIRECTORY_SEPARATOR;
        $this->excluded_dirs = [
            'vendor_path' => "{$this->root_dir}{$sep}vendor",
            'lib_path'    => "{$this->root_dir}{$sep}lib",
            'vendor_bcb'  => "{$this->root_dir}{$sep}modules{$sep}bcb{$sep}classes{$sep}vendor",
            'composer'    => "phar:",
        ];

        // Remove old classmap
        $this->removeFile($this->file_classmap);

        // Include all classes
        $this->includeAllClasses();
        $declared = array_merge(get_declared_classes(), get_declared_interfaces(), get_declared_traits());

        // Check shortname unicity (autoload alias)
        $_shortnames = [];

        // First loop => init classmap
        foreach ($declared as $key => $_class) {
            // Reflection
            try {
                $reflection = new ReflectionClass($_class);
            } catch (ReflectionException $e) {
                throw new CClassMapException("Catch ReflectionException : {$e->getMessage()}");
            }

            // Excluded
            if ($this->isExcluded($reflection)) {
                unset($declared[$key]);
                continue;
            }

            // Check namespaced
            if ($reflection->getNamespaceName() === '') {
                throw new CClassMapException("Invalid namespace for class {$reflection->getShortName()}");
            }

            // Check unicity (only for IShortNameAutoloadable)
            if ($reflection->implementsInterface(IShortNameAutoloadable::class)) {
                $short_name = $reflection->getShortName();
                $file_name  = $reflection->getFileName();
                if (array_key_exists($short_name, $_shortnames) && $_shortnames[$short_name] !== $file_name) {
                    throw new CClassMapException("Duplicate short name {$reflection->getShortName()}");
                } else {
                    $_shortnames[$short_name] = $file_name;
                }
            }

            // Check integrity
            if (strpos($reflection->getFileName(), $reflection->getShortName() . ".php") === false) {
                throw new CClassMapException("Invalid file name for class {$reflection->getShortName()}");
            }

            // Module
            preg_match(
                '/modules(\/|\\\)(?P<mod_name>.+)(\/|\\\)classes(\/|\\\)(.+).php$/',
                $reflection->getFileName(),
                $matches
            );

            $module = $matches['mod_name'] ?? null;

            $table = null;
            $key   = null;

            if ($reflection->isInstantiable() && $reflection->isSubclassOf(CStoredObject::class)) {
                // Object
                /** @var CStoredObject $object */
                $object = $reflection->newInstanceWithoutConstructor();

                try {
                    // Specs
                    $spec  = $object->getSpec();
                    $table = ($spec && $spec->table) ? $spec->table : null;
                    $key   = ($spec && $spec->key) ? $spec->key : null;
                } catch (Throwable $t) {
                    // Do nothing
                }
            }

            // Map
            $this->classmap[$reflection->getName()] = [
                'short_name'     => $reflection->getShortName(),
                'file'           => $reflection->getFileName(),
                'module'         => $module,
                'table'          => $table,
                'key'            => $key,
                'isTrait'        => $reflection->isTrait(),
                'isInterface'    => $reflection->isInterface(),
                'isInstantiable' => $reflection->isInstantiable(),
                'parent'         => $reflection->getParentClass() ? $reflection->getParentClass()->getName() : null,
                'interfaces'     => $reflection->getInterfaceNames(),
                'children'       => [],
            ];
        } // end foreach

        // Second loop => build lineage
        foreach ($this->classmap as $class_name => $_map) {
            // extends
            $parent = $_map['parent'];
            while ($parent !== null && isset($this->classmap[$parent])) {
                $this->classmap[$parent]['children'][] = $class_name;
                // parents tree
                $parent = $this->classmap[$parent]['parent'];
            }

            // implements (reflexion return all interfaces depth)
            foreach ($_map['interfaces'] as $interface) {
                if (isset($this->classmap[$interface])) {
                    $this->classmap[$interface]['children'][] = $class_name;
                }
            }
        }

        // Save classmap
        $content = var_export($this->classmap, true);
        $content = str_replace("'" . $this->root_dir, '$base_dir . \'', $content);
        $content = '<?php ' . PHP_EOL . ' $base_dir = dirname(dirname(__FILE__)); ' . PHP_EOL . ' return ' . $content . ';';

        if (!file_put_contents($this->file_classmap, $content)) {
            throw new CClassMapException("Unable to write file {$this->file_classmap}");
        }

        // Stop
        $_time  = @round(microtime(true) - $time_start, 3);
        $_count = @count($this->classmap);
        $_file  = $this->file_classmap;

        return "Generated classmap file in {$_file} containing {$_count} classes during {$_time} sec";
    }

    /**
     * @return void
     * @throws Exception
     */
    private function buildClassAlias(): void
    {
        try {
            $children = $this->getClassChildren(IShortNameAutoloadable::class);
        } catch (CClassMapException $e) {
            return;
        }

        foreach ($children as $full_name) {
            $_map                             = $this->classmap[$full_name];
            $this->alias[$_map['short_name']] = $full_name;
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    private function buildModules(): void
    {
        foreach ($this->classmap as $_map) {
            $_module = $_map['module'];
            if ($_module !== null) {
                $this->modules[$_module] = null;
            }
        }
        $this->modules = array_keys($this->modules);
        sort($this->modules);
    }

    private function buildModulesNamespaces()
    {
        foreach ($this->classmap as $_class => $_map) {
            $_module = $_map['module'];
            if ($_module !== null) {
                $this->modules_namespaces[$_module] = $_class;
            }
        }
        unset($_module, $_class);

        foreach ($this->modules_namespaces as $_module => $_class) {
            $this->modules_namespaces[$_module] = $this->getNamespace($_class, 0, 3);
        }
        unset($_module);
    }

    /**
     * @param ReflectionClass $reflector
     *
     * @return bool
     */
    private function isExcluded(ReflectionClass $reflector): bool
    {
        if ($reflector->isInternal()) {
            return true;
        }

        foreach ($this->excluded_dirs as $_path) {
            if (strpos($reflector->getFileName(), $_path) === 0) {
                return true;
            }
        }

        if (strpos($reflector->getNamespaceName(), 'Ox') !== 0) {
            return true;
        }

        return false;
    }

    /**
     * @param string|object $class
     *
     * @return string
     */
    public function isInterface($class): ?string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        try {
            return $this->getClassMap($class)->isInterface;
        } catch (Exception $e) {
            return $class;
        }
    }

    /**
     *
     * @return string
     * @throws CClassMapException
     * @throws ReflectionException
     */
    public function buildClassRef(): string
    {
        // Start
        $time_start = microtime(true);

        // Remove old classref
        $this->removeFile($this->file_classref);

        // Legacy
        global $dPconfig;
        include_once __DIR__ . '/../../includes/config_dist.php';
        $this->buildClassAlias();
        CAutoloadAlias::register();

        CSQLDataSource::$dataSources['std'] = null;
        CAppUI::$localize                   = false;
        CAppUI::$instance                   = new CAppUI();
        CAppUI::$instance->_ref_user        = new CMediusers();

        // Init
        $models         = $this->getClassMap(CModelObject::class)->children;
        $models[]       = CModelObject::class;
        $this->classref = [];
        $count_ref      = 0;
        $resource_types = []; // Check resource type unicity (api)

        // First loop build forward refs
        foreach ($models as $full_name) {
            // Reflection
            try {
                $reflection_class = new ReflectionClass($full_name);
            } catch (ReflectionException $e) {
                throw new CClassMapException($e->getMessage());
            }

            // Init
            $this->classref[$full_name] = [
                'fwd'  => [],
                'back' => [],
            ];

            // Can't access $instance->getProps()
            // Final classes cannot be instantiated without constructor
            // https://www.php.net/manual/en/reflectionclass.newinstancewithoutconstructor.php
            if (!$reflection_class->isInstantiable()) {
                continue;
            }

            // Moke initialize
            /** @var CModelObject $instance */
            $short_name       = $reflection_class->getShortName();
            $instance         = $reflection_class->isFinal()
                ? new $full_name()
                : $reflection_class->newInstanceWithoutConstructor();
            $instance->_class = $short_name;
            $instance->_spec  = $instance->getSpec();
            $instance->_props = $instance->getProps();

            // FieldSpec Factory
            try {
                /** @var array|CRefSpec $specs */
                $specs = $instance->getSpecs();
            } catch (Exception $e) {
                throw new CClassMapException($e->getMessage());
            }

            // Fields specifications
            foreach ($specs as $spec_name => $ref_spec) {
                // only ref
                if (!$ref_spec instanceof CRefSpec) {
                    continue;
                }

                // exclude primary key ref
                if ($spec_name === $instance->_spec->key) {
                    continue;
                }

                // exclude formfields
                if (strpos($spec_name, '_') === 0) {
                    continue;
                }

                // Count ref
                if (!$instance->isModelObjectAbstract()) {
                    $count_ref++;
                }

                // Backname required
                if (!$ref_spec->back) {
                    // Allow undefined
                    if (
                        ($full_name === CExObject::class)
                        || ($ref_spec->class === 'CExObject')
                        || $instance->isModelObjectAbstract()
                    ) {
                        $ref_spec->back = str_replace('.', '', uniqid('undefined_', true));
                    } else {
                        throw new CClassMapException("Missing back name in '{$short_name}-{$ref_spec->fieldName}'");
                    }
                }

                // Meta ref
                $meta_class = null;
                $meta_spec  = null;
                if ($meta = $ref_spec->meta) {
                    $meta_spec  = $specs[$meta];
                    $meta_class = $meta_spec instanceof CEnumSpec ? $meta_spec->_list : null;
                }

                // Check meta
                $checker = new CRefChecker($ref_spec, $meta_spec);
                try {
                    $checker->check();
                } catch (CRefCheckerException $e) {
                    $msg = $e->getCode() . '=>' . $e->getMessage();
                    echo $msg . PHP_EOL;
                }

                // Add fwd props
                $this->classref[$full_name]['fwd'][$ref_spec->fieldName] = [
                    'class' => $ref_spec->class,
                    'back'  => $ref_spec->back,
                    'meta'  => $meta_class,
                ];
            }

            // Check API resource name unicity
            $resource_type = $instance::RESOURCE_TYPE;
            if ($resource_type !== CModelObject::RESOURCE_TYPE) {
                if (in_array($resource_type, $resource_types, true)) {
                    throw new CClassMapException(
                        "Duplicate resource type '{$resource_type}' in class '{$instance->_class}'"
                    );
                }
                $resource_types[] = $resource_type;
            }
        }

        // Second loop build backref
        foreach ($this->classref as $full_name => $refs) {
            $short_name = $this->getShortName($full_name);

            foreach ($refs['fwd'] as $fwd_field => $fwd_spec) {
                // direct or meta ref ?
                $backs = $fwd_spec['meta'] ?? [$fwd_spec['class']];

                foreach ($backs as $back_class) {
                    // production is not a monorepo
                    if (!class_exists($back_class)) {
                        continue;
                    }

                    $back_fullname = $this->alias[$back_class];
                    // Only CModelObject reference
                    if (!array_key_exists($back_fullname, $this->classref)) {
                        throw new CClassMapException(
                            "Invalid '{$full_name}' reference, class '{$back_class}' must extends CModelObject"
                        );
                    }

                    // Check unicity backname
                    if (isset($this->classref[$back_fullname]['back'][$fwd_spec['back']])) {
                        throw new CClassMapException(
                            "Duplicate backname '{$fwd_spec['back']}' in class {$back_fullname} - {$short_name} {$fwd_field}"
                        );
                    }

                    // Add backprop
                    $this->classref[$back_fullname]['back'][$fwd_spec['back']] = "{$short_name} {$fwd_field}";
                }
            }
        }

        $content = var_export($this->classref, true);
        $content = '<?php ' . PHP_EOL . ' return ' . $content . ';';

        if (!file_put_contents($this->file_classref, $content)) {
            throw new CClassMapException("Unable to write file {$this->file_classref}");
        }

        // unset legacy
        $dPconfig         = null;
        CAppUI::$instance = null;
        CAutoloadAlias::unregister();

        // Stop
        $_time  = @round(microtime(true) - $time_start, 3);
        $_count = @count($this->classref);
        $_file  = $this->file_classref;

        $msg = "Generated classref file in {$_file} containing {$count_ref} references in {$_count} classes during {$_time} sec";

        return $msg;
    }

    /**
     *
     * @return string
     * @throws CClassMapException
     * @throws ReflectionException
     */
    public function buildLegacyActions(): string
    {
        // Start
        $time_start = microtime(true);

        // Remove old legacy_actions
        $this->removeFile($this->file_legacy_actions);

        // Init
        $controllers = $this->getClassMap(CLegacyController::class)->children;

        [$count_actions, $count_controllers] = $this->processLegacyActions($controllers);

        $content = var_export($this->legacy_actions, true);
        $content = '<?php ' . PHP_EOL . ' return ' . $content . ';';

        if (!file_put_contents($this->file_legacy_actions, $content)) {
            throw new CClassMapException("Unable to write file {$this->file_legacy_actions}");
        }

        // Stop
        $_time = @round(microtime(true) - $time_start, 3);
        $_file = $this->file_legacy_actions;

        $msg = "Generated legacy_actions file in {$_file} containing {$count_actions} actions in {$count_controllers} controllers during {$_time} sec";

        return $msg;
    }

    private function processLegacyActions(array $controllers): array
    {
        $count_actions     = 0;
        $count_controllers = 0;

        // Process
        foreach ($controllers as $controller) {
            // Excluded controller
            if ($controller === CMainController::class) {
                continue;
            }

            // Reflection
            try {
                $reflection_class = new ReflectionClass($controller);
            } catch (ReflectionException $e) {
                throw new CClassMapException($e->getMessage());
            }

            // Only instanciable controller
            if ($reflection_class->isInstantiable() === false) {
                continue;
            }

            // Check controller namespace
            if (strpos($controller, 'Controllers\Legacy') === false) {
                throw new CClassMapException(
                    "Invalid namespace legacyController, must be in 'Controllers\Legacy' folder : {$controller}"
                );
            }

            $count_controllers++;
            $_module = $this->getClassMap($controller)->module;
            if (!array_key_exists($_module, $this->legacy_actions)) {
                $this->legacy_actions[$_module] = [];
            }

            $methods = $reflection_class->getMethods();

            foreach ($methods as $method) {
                $_action = $method->getName();

                // only public and non static methods
                if (!$method->isPublic() || $method->isStatic()) {
                    continue;
                }

                // todo check if parameters have default value

                // check unicity actions name
                if (array_key_exists($_action, $this->legacy_actions[$_module])) {
                    throw new CClassMapException("Duplicate module action name {$_module} => {$_action}");
                }

                $this->legacy_actions[$_module][$_action] = $controller;
                $count_actions++;
            }
        }

        return [$count_actions, $count_controllers];
    }

    /**
     * @param $module
     *
     * @return array|string
     */
    public function getLegacyActions(string $module = null): array
    {
        // Set legacy actions only when necessary
        if (empty($this->legacy_actions) && file_exists($this->file_legacy_actions)) {
            $this->legacy_actions = include $this->file_legacy_actions;
        }

        if ($module) {
            return $this->legacy_actions[$module] ?? [];
        }

        return $this->legacy_actions;
    }

    /**
     * @return void
     * @throws CClassMapException
     */
    private function includeAllClasses(): void
    {
        if (!is_dir($this->root_dir)) {
            throw new CClassMapException("Invalid root_dir {$this->root_dir}");
        }

        foreach ($this->dirs as $_dir) {
            $_dir = $this->root_dir . DIRECTORY_SEPARATOR . $_dir;
            $this->globAndInculdeFiles($_dir);
        }
    }

    /**
     * Recursive function
     */
    private function globAndInculdeFiles(string $path): void
    {
        // exclude vendor directory (bcb)
        $dirs = glob($path . DIRECTORY_SEPARATOR . '[!vendor]*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $this->globAndInculdeFiles($dir);
        }

        $files = glob($path . DIRECTORY_SEPARATOR . '*' . self::EXTENSION_PHP);
        foreach ($files as $file) {
            include_once $file;
        }
    }

    /**
     * @param null|string|object $class
     *
     * @return array|stdClass
     * @throws Exception
     */
    public function getClassMap($class = null)
    {
        return $this->getClassDefinition(static::DEFINITION_MAP, $class);
    }

    /**
     * @param null|string|object $class
     *
     * @return array|stdClass
     * @throws Exception
     */
    public function getClassRef($class = null)
    {
        return $this->getClassDefinition(static::DEFINITION_REF, $class);
    }

    /**
     * @param null|string|object $class
     * @param string             $type
     *
     * @return mixed
     * @throws CClassMapException
     */
    private function getClassDefinition($type, $class = null)
    {
        $arry_to_search = $type === static::DEFINITION_MAP ? 'classmap' : 'classref';

        if (!$class) {
            return $this->$arry_to_search;
        }

        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!is_array($this->$arry_to_search) || !array_key_exists($class, $this->$arry_to_search)) {
            throw new CClassMapException("Invalid {$arry_to_search} index : {$class}");
        }


        return (object)$this->{$arry_to_search}[$class];
    }

    /**
     * @param string $class
     * @param bool   $return_instance
     * @param bool   $only_instantiable
     * @param string $module
     *
     * @return mixed
     * @throws Exception
     */
    public function getClassChildren($class, $return_instance = false, $only_instantiable = false, $module = null)
    {
        $children = $this->getClassMap($class)->children;
        sort($children);

        if ($only_instantiable) {
            $children = array_filter(
                $children,
                function ($child) {
                    return (bool)$this->getClassMap($child)->isInstantiable;
                }
            );
        }

        if ($module) {
            $children = $children = array_filter(
                $children,
                function ($child) use ($module) {
                    return (bool)($this->getClassMap($child)->module == $module);
                }
            );
        }

        if ($return_instance) {
            foreach ($children as &$child) {
                $child = new $child();
            }
        }

        return $children;
    }

    /**
     * @param string $class The class name
     *
     * @return array
     * @throws Exception
     */
    public function getDirectClassChildren($class): array
    {
        $map = $this->getClassMap($class);
        if ($map->isInterface) {
            throw new CClassMapException('Direct class children is not compatible with Interface');
        }
        $_direct_children = [];
        foreach ($this->classmap as $class_name => $_map) {
            if ($_map['parent'] === $class) {
                $_direct_children[] = $class_name;
            }
        }

        return $_direct_children;
    }


    /**
     * @param string $short_name
     *
     * @return string
     * @throws Exception
     */
    public function getAliasByShortName($short_name): string
    {
        if (array_key_exists($short_name, $this->alias)) {
            return $this->alias[$short_name];
        }

        return false;
    }


    /**
     * @return array
     */
    public function getDirs(): array
    {
        return $this->dirs;
    }

    /**
     * Get the shortname of a class
     *
     * @param string|object $class Name or Instanceof the class to get shortname
     *
     * @return string
     *
     * @throws Exception
     */
    public static function getSN($class): string
    {
        return self::getInstance()->getShortName($class);
    }


    /**
     * @param string|object $class
     * @param int           $offset
     * @param int|null      $length
     *
     * @return string
     */
    public function getNamespace($class, int $offset = 0, ?int $length = null): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $ns = str_replace('\\' . $this->getShortName($class), '', $class);
        if ($offset || $length) {
            $ns = explode('\\', $ns);
            $ns = $length ? array_splice($ns, $offset, $length) : array_splice($ns, $offset);
            $ns = implode('\\', $ns);
        }

        return $ns;
    }


    /**
     * Get the shortname of a class
     *
     * @param string|object $class Name or instance of the class to get shortname
     *
     * @return string
     */
    public function getShortName($class): ?string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        try {
            return $this->getClassMap($class)->short_name;
        } catch (Exception $e) {
            return $class;
        }
    }

    /**
     * @return string|null
     */
    public function getClassMapFile(): ?string
    {
        return $this->file_classmap;
    }


    /**
     * @return string|null
     */
    public function getClassRefFile(): ?string
    {
        return $this->file_classref;
    }

    /**
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    public function getModulesNamespaces(): array
    {
        if (empty($this->modules_namespaces)) {
            $this->buildModulesNamespaces();
        }

        return $this->modules_namespaces;
    }

    /**
     * @param string $namespace
     *
     * @return string|null
     */
    public function getModuleFromNamespace(string $namespace)
    {
        return array_search($namespace, $this->getModulesNamespaces(), true) ?: null;
    }

    /**
     * @param string $module
     *
     * @return string|null
     */
    public function getNamespaceFromModule(string $module)
    {
        return $this->getModulesNamespaces()[$module] ?? null;
    }

    /**
     * @return array
     */
    public function getAlias()
    {
        return $this->alias;
    }

    private function removeFile(string $file_path): bool
    {
        if (file_exists($file_path) && is_file($file_path)) {
            return unlink($file_path);
        }

        return false;
    }
}
