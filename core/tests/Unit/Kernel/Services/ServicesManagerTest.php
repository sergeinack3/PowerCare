<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Kernel\Services;

use Ox\Core\Kernel\Services\ServicesManager;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;

/**
 * Tests for the aggregation of services
 */
class ServicesManagerTest extends OxUnitTestCase
{
    /**
     * Ensure the base structure is not changed by mistake
     */
    public function testBuildDefaultContent(): void
    {
        $manager = new ServicesManager();
        $this->assertEmpty($this->getPrivateProperty($manager, 'content'));

        $this->invokePrivateMethod($manager, 'buildDefaultContent');

        $this->assertEquals(
            [
                ServicesManager::IMPORTS_ROOT_NODE  => [],
                ServicesManager::SERVICES_ROOT_NODE => [
                    ServicesManager::DEFAULT_NODE => [
                        ServicesManager::DEFAULT_AUTOWIRE      => true,
                        ServicesManager::DEFAULT_AUTOCONFIGURE => true,
                    ],
                ],
            ],
            $this->getPrivateProperty($manager, 'content')
        );
    }

    public function testGetNamespace(): void
    {
        $manager = new ServicesManager();

        $this->assertNull($this->invokePrivateMethod($manager, 'getNamespace', []));
        $this->assertNull(
            $this->invokePrivateMethod(
                $manager,
                'getNamespace',
                [
                    ServicesManager::COMPOSER_AUTOLOAD => [
                        ServicesManager::COMPOSER_PSR => [
                            'Ox\\Mediboard\\System\\Tests\\' => "modules/system/tests",
                        ],
                    ],
                ]
            )
        );

        $this->assertEquals(
            'Ox\\Mediboard\\System\\Controllers\\',
            $this->invokePrivateMethod(
                $manager,
                'getNamespace',
                [
                    ServicesManager::COMPOSER_AUTOLOAD => [
                        ServicesManager::COMPOSER_PSR => [
                            'Ox\\Mediboard\\System\\Tests\\' => "modules/system/tests",
                            'Ox\\Mediboard\\System\\'        => "modules/system/classes",
                        ],
                    ],
                ]
            )
        );
    }

    public function testAddImportsEmpty(): void
    {
        $manager = new ServicesManager();
        $this->assertEmpty($this->getPrivateProperty($manager, 'content'));
        $this->invokePrivateMethod($manager, 'addImports');
        $this->assertEmpty($this->getPrivateProperty($manager, 'content'));
    }

    public function testAddImports(): void
    {
        $manager = new ServicesManager();
        $this->assertEmpty($this->getPrivateProperty($manager, 'content'));

        $reflection = new ReflectionClass($manager);
        $prop       = $reflection->getProperty('services_dirs');
        $prop->setAccessible(true);
        $prop->setValue($manager, ['modules/system/services', 'core/services']);

        $this->invokePrivateMethod($manager, 'addImports');

        $this->assertEquals(
            [
                ServicesManager::IMPORTS_ROOT_NODE => [
                    [ServicesManager::NODE_RESOURCE => '..modules/system/services'],
                    [ServicesManager::NODE_RESOURCE => '..core/services'],
                ],
            ],
            $this->getPrivateProperty($manager, 'content')
        );
    }

    public function testBuildAllServices(): void
    {
        $manager = $this->getMockBuilder(ServicesManager::class)
            ->onlyMethods(['removeOldFile', 'writeFile'])
            ->getMock();

        $ret = $manager->buildAllServices();

        preg_match(
            '/^Generated services file in (?<file_path>\S+) containing (?<count_services>\d+) services/i',
            $ret,
            $matches
        );

        $this->assertTrue($matches['count_services'] > 0);
    }
}
