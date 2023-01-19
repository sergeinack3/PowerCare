<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\CClassMap;
use Ox\Core\COracleDataSource;
use Ox\Core\CPDODataSource;
use Ox\Core\CPDOMySQLDataSource;
use Ox\Core\CPDOODBCDataSource;
use Ox\Core\CPDOOracleDataSource;
use Ox\Core\CPDOSQLServerDataSource;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Search\CSearchHistory;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class CClassMapTest extends OxUnitTestCase
{

    public function testBuildMap()
    {
        $classmap = CClassMap::getInstance();
        $classmap->buildClassMap();
        $this->assertNotEmpty($classmap->getClassMap());
    }

    /**
     *
     * @throws Exception
     */
    public function testMap()
    {
        $classmap = CClassMap::getInstance();
        $map      = $classmap->getClassMap(CSQLDataSource::class);
        $this->assertEquals($map->isInterface, false);
    }

    /**
     *
     * @throws Exception
     */
    public function testInternalMap()
    {
        $classmap = CClassMap::getInstance();
        $this->expectException(Exception::class);
        $classmap->getClassMap("DateTime");
    }


    /**
     * @throws Exception
     */
    public function testChildren()
    {
        $classmap = CClassMap::getInstance();
        $children = $classmap->getClassChildren(CSQLDataSource::class);
        $this->assertEquals(
            $children,
            [
                COracleDataSource::class,
                CPDODataSource::class,
                CPDOMySQLDataSource::class,
                CPDOODBCDataSource::class,
                CPDOOracleDataSource::class,
                CPDOSQLServerDataSource::class,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function testChildrenFromModule()
    {
        $classmap = CClassMap::getInstance();
        $children = $classmap->getClassChildren(CStoredObject::class, false, false, 'system');
        foreach ($children as $_child) {
            $class_child = $classmap->getClassMap($_child);
            $this->assertEquals('system', $class_child->module);
        }
    }

    /**
     * @throws Exception
     */
    public function testShortName()
    {
        $classmap   = CClassMap::getInstance();
        $patient    = new CSearchHistory();
        $short_name = $classmap->getShortName($patient);

        $this->assertEquals($short_name, 'CSearchHistory');
    }

    public function testInstanceAllClasses()
    {
        $maps = CClassMap::getInstance()->getClassMap();

        $instances = [];
        foreach ($maps as $class_name => $map) {
            if ($map['isInstantiable']) {
                $r = new ReflectionClass($class_name);

                // Cannot instantiate final classes without constructor
                // https://www.php.net/manual/en/reflectionclass.newinstancewithoutconstructor.php
                if (!$r->isFinal()) {
                    $instances[] = $r->newInstanceWithoutConstructor();
                }
            }
        }

        $this->assertEquals(count($maps), count($maps));
    }

    /**
     * @todo incomplete
     */
    //  public function testInstanceWithConstructAllClasses() {
    //    $this->markTestSkipped();
    //    $maps = CClassMap::getInstance()->getClassMap();
    //
    //    $instances = array();
    //    foreach ($maps as $class_name => $map) {
    //      if (!$map['isInstantiable']) {
    //        continue;
    //      }
    //
    //      try {
    //        $r = new ReflectionClass($class_name);
    //      }
    //      catch (ReflectionException $e) {
    //        $this->fail("ReflectionException");
    //      }
    //
    //      $args        = array();
    //      $constructor = $r->getConstructor();
    //      if ($constructor && !$constructor->isInternal()) {
    //        foreach ($constructor->getParameters() as $parameter) {
    //          $position = $parameter->getPosition();
    //
    //          if ($parameter->isOptional()) {
    //            continue;
    //          }
    //
    //          $value = null;
    //          try {
    //            $value = $parameter->getDefaultValue();
    //          }
    //          catch (ReflectionException $default_value) {
    //            if ($parameter->allowsNull()) {
    //              $value = null;
    //            }
    //            else {
    //              if ($type = $parameter->getType()) {
    //                switch ($type) {
    //                  case 'int' :
    //                    $value = uniqid();
    //                    break;
    //                  case 'string':
    //                    $value = 'loremipsum';
    //                    break;
    //                  case 'array':
    //                    $value = array();
    //                    break;
    //                  default :
    //                    $value = null;
    //                }
    //              }
    //            }
    //          }
    //
    //          $args[$position] = $value;
    //
    //          // reference
    //          if ($parameter->isPassedByReference()) {
    //            $args[$position] = "&" . $args[$position];
    //          }
    //        }
    //      };
    //      $instances[] = $r->newInstanceArgs($args);
    //    }
    //
    //    $this->assertEquals(count($maps), count($maps));
    //  }

    /**
     * @throws ReflectionException
     * @throws \Ox\Core\CClassMapException
     * @todo active when ref initialize CModelObejct
     */
    //  public function testBuildRef() {
    //    $this->markTestSkipped('active when ref initialize CModelObejct');
    //    $classmap = CClassMap::getInstance();
    //    $classmap->buildClassRef();
    //    $this->assertNotEmpty($classmap->getClassRef());
    //  }

    /**
     *
     * @throws Exception
     */
    public function testRef()
    {
        $classmap = CClassMap::getInstance();
        $ref      = $classmap->getClassRef(CStoredObject::class);
        $this->assertTrue(is_array($ref->back));

        return $ref;
    }

    public function testAlias()
    {
        $classmap = CClassMap::getInstance();
        $this->assertArrayHasKey('CUser', $classmap->getAlias());
        $this->assertEquals($classmap->getAliasByShortName('CUser'), CUser::class);
    }


    public function testModules()
    {
        $classmap = CClassMap::getInstance();
        $this->assertContains('system', $classmap->getModules());
    }


    /**
     * @depends testRef
     * @throws Exception
     */
    public function testCStoredObjectBack($ref)
    {
        $_backprops = $ref->back;
        foreach ($_backprops as $back_key => $back_value) {
            if (strpos($back_key, 'undefined_') === 0) {
                unset($_backprops[$back_key]);
            }
        }

        // Why hard-coded?
        $this->assertEquals(count($_backprops), 6);
    }

    public function testProcessLegacyActions(): void
    {
        $class_map = CClassMap::getInstance();
        $this->addClassesToMap(
            [
                'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumLegacyController'         => [
                    'module' => 'core',
                ],
                'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumInheritLegacyController'  => [
                    'module' => 'core',
                ],
                'Ox\Core\Tests\Resources\Controllers\Legacy\AbstractCLoremIpsumLegacyController' => [
                    'module' => 'core',
                ],
                'Ox\Mediboard\System\Controllers\Legacy\CMainController'                         => [
                    'module' => 'core',
                ],
                'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumSecondLegacyController'   => [
                    'module' => 'dPpatients',
                ],
            ]
        );

        $controllers = [
            'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumLegacyController',
            'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumInheritLegacyController',
            'Ox\Core\Tests\Resources\Controllers\Legacy\AbstractCLoremIpsumLegacyController',
            'Ox\Mediboard\System\Controllers\Legacy\CMainController',
            'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumSecondLegacyController',
        ];

        $this->invokePrivateMethod($class_map, 'processLegacyActions', $controllers);

        $result = [
            'core'       => [
                'action_foo'     => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumLegacyController',
                'action_inherit' => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumInheritLegacyController',
                'action_test'    => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumInheritLegacyController',
            ],
            'dPpatients' => [
                'action_foo' => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumSecondLegacyController',
            ],
        ];

        $this->assertEquals($result, $class_map->getLegacyActions());
    }

    public function testProcessLegacyActionNameSpaceRestriction(): void
    {
        $class_map = CClassMap::getInstance();

        $this->addClassesToMap(
            [
                'Ox\Core\Tests\Resources\CLoremIpsumLegacyController' => [
                    'module' => 'core',
                ],
            ]
        );

        $controllers = ['Ox\Core\Tests\Resources\CLoremIpsumLegacyController',];

        $this->expectExceptionMessage(
            "Invalid namespace legacyController, must be in 'Controllers\Legacy' folder : Ox\Core\Tests\Resources\CLoremIpsumLegacyController"
        );
        $this->invokePrivateMethod($class_map, 'processLegacyActions', $controllers);
    }

    public function testProcessLegacyActionDuplicationModuleAction(): void
    {
        $class_map = CClassMap::getInstance();

        $this->addClassesToMap(
            [
                'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumLegacyController'       => [
                    'module' => 'core',
                ],
                'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumSecondLegacyController' => [
                    'module' => 'core',
                ],
            ]
        );

        $controllers = [
            'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumLegacyController',
            'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumSecondLegacyController',
        ];

        $this->expectExceptionMessage("Duplicate module action name core => action_foo");
        $this->invokePrivateMethod($class_map, 'processLegacyActions', $controllers);
    }

    /**
     * @dataProvider getLegacyActionsProvider
     */
    public function testGetLegacyActions($module, $expected_actions): void
    {
        $class_map    = CClassMap::getInstance();
        $property_ref = new ReflectionProperty($class_map, 'file_legacy_actions');
        $property_ref->setAccessible(true);
        $property_ref->setValue($class_map, dirname(__DIR__, 3) . '/core/tests/Resources/test_legacy_actions.php');
        $property_ref->setAccessible(false);

        $this->assertEquals($expected_actions, $class_map->getLegacyActions($module));
    }

    public function getLegacyActionsProvider(): array
    {
        return [
            'no_module'              => [null, $this->getAllActionsFromMockFile()],
            'core'                   => ['core', $this->getAllActionsFromMockFile('core')],
            'module_does_not_exists' => ['toto', $this->getAllActionsFromMockFile('toto')],
        ];
    }

    private function getAllActionsFromMockFile(string $module = null): array
    {
        $actions = [
            'core'       => [
                'action_foo'     => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumLegacyController',
                'action_inherit' => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumInheritLegacyController',
                'action_test'    => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumInheritLegacyController',
            ],
            'dPpatients' => [
                'action_foo' => 'Ox\Core\Tests\Resources\Controllers\Legacy\CLoremIpsumSecondLegacyController',
            ],
        ];

        if (!$module) {
            return $actions;
        }

        return $actions[$module] ?? [];
    }

    public function testGetModuleFromNS()
    {
        $class_map = CClassMap::getInstance();
        $this->assertEquals('dPdeveloppement', $class_map->getModuleFromNamespace('Ox\Mediboard\Developpement'));
    }

    public function testGetNSFromModule()
    {
        $class_map = CClassMap::getInstance();
        $this->assertEquals('Ox\Mediboard\Developpement', $class_map->getNamespaceFromModule('dPdeveloppement'));
    }

    public function testGetNamespace()
    {
        $class_map  = CClassMap::getInstance();
        $reflection = new ReflectionClass(CUser::class);
        $this->assertEquals($reflection->getNamespaceName(), $class_map->getNamespace(CUser::class));
        $expected = str_replace('Ox\\', '', $reflection->getNamespaceName());
        $this->assertEquals($expected, $class_map->getNamespace(CUser::class, 1));
        $this->assertEquals('Mediboard\Admin', $class_map->getNamespace(CUser::class, 1, 2));
    }
}
