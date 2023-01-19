<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\CStoredObject;
use Ox\Erp\SourceCode\CFixturesReference;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CConfiguration;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Request;

/**
 * horizontal composition of behavior (config, pref, fixtures ...)
 */
trait OxTestTrait
{
    // Configs
    private $newConfigs = ['standard' => [], 'groups' => [], 'static' => []];
    private $oldConfigs = ['standard' => [], 'groups' => [], 'static' => []];

    // Prefs
    private $newPrefs = ['standard' => []];
    private $oldPrefs = [];

    // Fixtures
    private static $object_from_fixtures = [];


    /**
     * Add classes to class map
     *
     * @param array $classes
     *
     * @throws ReflectionException
     */
    public function addClassesToMap(array $classes): void
    {
        $class_map    = CClassMap::getInstance();
        $property_ref = new ReflectionProperty($class_map, 'classmap');
        $property_ref->setAccessible(true);

        $property_ref->setValue($class_map, array_merge($property_ref->getValue($class_map), $classes));

        $property_ref->setAccessible(false);
    }

    /**
     * Set a standard configuration (config.php or config_db)
     *
     * @param string $path  Configuration path
     * @param mixed  $value Configuration value
     *
     * @return array
     */
    private static function setStandardConfig(string $path, $value): array
    {
        // Getting old value
        $parts     = explode(' ', $path);
        $first     = array_shift($parts);
        $old_value = ($GLOBALS['dPconfig'][$first]) ?? [];

        foreach ($parts as $_part) {
            $old_value = ($old_value[$_part]) ?? [];
        }

        // Values are scalars
        $old_value = (is_array($old_value)) ? null : $old_value;

        // Setting new value
        $parts = explode(' ', $path);
        $node  = &$GLOBALS['dPconfig'];

        foreach ($parts as $_key) {
            $node = &$node[$_key];
        }

        $node = $value;

        return [$path => $old_value];
    }

    /**
     * @param string $object_class
     * @param string $tag
     * @param bool   $clone
     *
     * @return CStoredObject
     * @throws TestsException
     * @throws Exception
     */
    public function getObjectFromFixturesReference(
        string $object_class,
        string $tag,
        bool $clone = false
    ): CStoredObject {
        if (!isset(self::$object_from_fixtures[$object_class][$tag])) {
            if (!is_subclass_of($object_class, CStoredObject::class)) {
                throw new TestsException("{$object_class} is not a subclass_of CStoredObject");
            }

            /** @var CStoredObject $target */
            $target           = new $object_class();
            $fr               = new CFixturesReference();
            $fr->object_class = $target->_class;
            $fr->tag          = $tag;
            $fr->loadMatchingObject();

            if (!$fr->_id) {
                throw new TestsException("Undefined reference {$object_class} with tag {$tag}");
            }

            $target->load($fr->object_id);
            if (!$target->_id) {
                throw new TestsException("Undefined object {$object_class} with id {$target->_id}");
            }

            self::$object_from_fixtures[$object_class][$tag] = $target;
        }

        $object = self::$object_from_fixtures[$object_class][$tag];

        // If true, return a new stored object with same values as object loaded from fixture
        if ($clone) {
            return $this->cloneModelObject($object);
        }

        return $object;
    }

    /**
     * Set MB configuration according to test comment
     *
     * @param array $configs Array containing config path, value and type (standard or groups)
     *
     * @return void
     */
    protected function setConfig($configs)
    {
        if (empty($configs['standard']) && empty($configs['groups']) && empty($configs['static'])) {
            return;
        }

        foreach ($configs as $_type => $_configs) {
            foreach ($_configs as $_path => $_value) {
                if ($_type == 'standard') {
                    // todo change  in GLOBAL $dpconfig
                    $this->oldConfigs['standard'][] = static::setStandardConfig($_path, $_value);
                } elseif ($_type === 'static') {
                    $this->oldConfigs['static'][] = static::setStaticConfig($_path, $_value);
                } else {
                    // todo change in cache static
                    $this->oldConfigs['groups'][] = static::setGroupsConfig($_path, $_value);
                }
            }
        }
    }

    /**
     * Get the current test function comments
     *
     * @return null|string
     */
    private function getFunctionComments()
    {
        global $mbpath;
        $mbpath = __DIR__ . "/../../";

        // HTTP_HOST is undefined when running with PHP CLI
        $_SERVER["HTTP_HOST"] = "";

        $reflectionClass = new ReflectionClass(get_class($this));

        $method_name = $this->getName();
        if (!$reflectionClass->hasMethod($method_name)) {
            // @dataProvider case
            $method_name = explode(' ', $this->getName())[0];
            if (!$reflectionClass->hasMethod($method_name)) {
                return;
            }
        }

        $method = $reflectionClass->getMethod($method_name);

        return $method->getDocComment();
    }

    /**
     * Parse function comment in order to retrieve config information
     *
     * @param string $type  Type of comment to parse (config or pref)
     * @param array  $array Array to append parsed values
     *
     * @return array
     */
    private function parseComment($type, &$array)
    {
        $comments = $this->getFunctionComments();

        if (preg_match_all("/.*{$type}\s(?<{$type}>.*)/", $comments, $matches)) {
            foreach ($matches[$type] as $_match) {
                $pos   = strrpos($_match, ' ');
                $path  = trim(substr($_match, 0, $pos));
                $value = trim(substr($_match, $pos + 1));

                // Needed fo groups config
                if ($pos = strpos($path, '[CConfiguration]') !== false) {
                    $path = str_replace('[CConfiguration] ', '', $path);

                    if (strpos($path, '[static]') === 0) {
                        $array['static'] += [str_replace('[static] ', '', $path) => $value];
                    } else {
                        $array['groups'] += [$path => $value];
                    }
                } else {
                    $array['standard'] += [$path => $value];
                }
            }
        }

        return $array;
    }

    /**
     * Sets a groups configuration (CConfiguration class)
     * The value is set for global and all groups
     *
     * @param string $path  Configuration path
     * @param mixed  $value Configuration value
     *
     * @return string
     */
    private static function setGroupsConfig(string $path, $value): string
    {
        CConfiguration::setValueInCache($path, $value, CGroups::loadCurrent()->_guid);

        return $path;
    }

    private static function setStaticConfig(string $path, $value): string
    {
        CConfiguration::setValueInCache($path, $value, 'static');

        return $path;
    }

    protected function createRequestApi(string $route_name = null): Request
    {
        $request = new Request([], [], ['_route' => $route_name]);

        $reflection = new ReflectionClass($request);
        $prop       = $reflection->getProperty('pathInfo');
        $prop->setAccessible(true);
        $prop->setValue($request, '/api/status');

        return $request;
    }

    public function storeOrFailed(CStoredObject $object)
    {
        if ($msg = $object->store()) {
            $this->fail($msg);
        }
    }

    public function deleteOrFailed(CStoredObject $object)
    {
        if ($msg = $object->delete()) {
            $this->fail($msg);
        }
    }
}
