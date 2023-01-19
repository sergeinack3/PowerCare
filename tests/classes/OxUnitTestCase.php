<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Tests;

use Ox\Core\CAppUI;
use Ox\Core\CModelObject;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Kernel;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\System\CConfiguration;
use PHPUnit\Framework\TestCase;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;

class OxUnitTestCase extends TestCase
{
    use OxTestTrait;
    use OxAssertionsTrait;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        CModelObject::$spec = [];

        $this->setConfig($this->parseComment('config', $this->newConfigs));
        $this->setPref($this->parseComment('pref', $this->newPrefs));
    }

    /**
     * @param array $preferences
     *
     * @return void
     */
    protected function setPref($preferences)
    {
        if (!$preferences) {
            return;
        }
        foreach ($preferences['standard'] as $_key => $_value) {
            CAppUI::$instance->user_prefs[$_key] = $_value;
        }
    }


    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        foreach ($this->oldConfigs['standard'] as $_old_values) {
            foreach ($_old_values as $_path => $_old_value) {
                static::setStandardConfig($_path, $_old_value);

                unset($this->oldConfigs[$_path]);
            }
        }

        CConfiguration::removeValuesFromCache($this->oldConfigs['groups'], CGroups::loadCurrent()->_guid);
        CConfiguration::removeValuesFromCache($this->oldConfigs['static'], 'static');
        $this->oldConfigs['groups'] = [];
        $this->oldConfigs['static'] = [];

        parent::tearDown();
    }

    /**
     * @param mixed  $object      Classname or object (instance of the class) that contains the method.
     * @param string $method_name Name of the method.
     * @param array  $params      Parameters of the method (Variable-length argument lists )
     *
     * @return mixed The method result.
     * @throws TestsException|ReflectionException
     */
    public function invokePrivateMethod($object, $method_name, ...$params)
    {
        // Obj
        if (!is_object($object)) {
            if (!class_exists($object)) {
                throw new TestsException('The class does not exist ' . $object);
            }
            $object = new $object;
        }

        // Reflection
        try {
            $method = new ReflectionMethod($object, $method_name);
        } catch (ReflectionException $e) {
            throw new TestsException('The method does not exist ' . $e->getMessage());
        }

        // Accessibility
        if ($method->isPublic()) {
            throw new TestsException('Method is already public');
        }
        $method->setAccessible(true);

        // Invoke
        if ($method->isStatic()) {
            $object = null;
        }

        return $method->invoke($object, ...$params);
    }

    /**
     * @param mixed  $object     Classname or object (instance of the class) that contains the constant.
     * @param string $const_name Name of the constant to get value from.
     *
     * @return mixed The constant value
     * @throws TestsException
     */
    public function getPrivateConst($object, $const_name)
    {
        // Obj
        if (!is_object($object)) {
            if (!class_exists($object)) {
                throw new TestsException('The class does not exist ' . $object);
            }
            $object = new $object;
        }

        // Reflection
        try {
            $const = new ReflectionClassConstant($object, $const_name);
        } catch (ReflectionException $e) {
            throw new TestsException('The constant does not exist ' . $e->getMessage());
        }

        // Accessibility
        if ($const->isPublic()) {
            throw new TestsException('Constant is already public');
        }

        return $const->getValue();
    }

    /**
     * @param mixed  $object          Classname or object (instance of the class) that contains the constant.
     * @param string $property        Name of the property
     * @param bool   $return_property Retrun the value or the object
     *
     * @return mixed The constant value
     * @throws TestsException
     */
    public function getPrivateProperty($object, string $property, bool $return_property = false)
    {
        // Obj
        if (!is_object($object)) {
            if (!class_exists($object)) {
                throw new TestsException('The class does not exist ' . $object);
            }
            $object = new $object;
        }

        // Reflection
        try {
            $reflection = new \ReflectionClass($object);
            $property   = $reflection->getProperty($property);
        } catch (ReflectionException $e) {
            throw new TestsException('The property does not exist ' . $e->getMessage());
        }

        // Accessibility
        if ($property->isPublic()) {
            throw new TestsException('Property is already public');
        }
        $property->setAccessible(true);

        if ($return_property) {
            return $property;
        }

        return $property->getValue($object);
    }

    /**
     * @param CModule $module
     *
     * @return mixed
     * @throws TestsException
     */
    public static function toogleActiveModule(CModule $module)
    {
        $module->mod_active = 1 - $module->mod_active;

        $msg = $module->store();
        if ($msg) {
            throw new TestsException($msg);
        }

        if (array_key_exists($module->mod_name, CModule::$active)) {
            unset(CModule::$active[$module->mod_name]);
        } else {
            CModule::$active[$module->mod_name] = $module;
        }
    }

    /**
     * Clone and object and store it if cloned object is a CStoredObject
     */
    public function cloneModelObject(CModelObject $object)
    {
        $new_object = new $object->_class();
        $new_object->cloneFrom($object);

        if ($object instanceof CStoredObject) {
            $this->storeOrFailed($new_object);
        }

        return $new_object;
    }

}
