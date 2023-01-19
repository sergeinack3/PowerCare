<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Controllers;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\Kernel\Exception\AccessDeniedException;
use Ox\Core\Module\CModule;
use Ox\Erp\COXEquipe;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Populate\Generators\CServiceGenerator;
use Ox\Mediboard\System\CConfiguration;
use Ox\Mediboard\System\CConfigurationModelManager;
use Ox\Mediboard\System\ConfigurationManager;
use Ox\Mediboard\System\Controllers\ConfigurationController;
use Ox\Tests\OxUnitTestCase;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;


class ConfigurationControllerTest extends OxUnitTestCase
{
    public function testListConfigurationOnModuleWithNoRead(): void
    {
        $mock = $this->getMockBuilder(ConfigurationController::class)
            ->onlyMethods(['checkPermRead'])
            ->getMock();

        $mock->method('checkPermRead')->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $mock->listConfigurations('system', $this->getRequestApi());
    }

    public function testGetInstanceConfigurations(): void
    {
        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', 'system');

        $this->assertEquals(
            CMbArray::flattenArrayKeys(CAppUI::conf('system'), 'system'),
            $this->invokePrivateMethod($controller, 'getInstanceConfigurations', $this->getRequestApi())
        );
    }

    public function testGetInstanceConfigurationsForNonExistingModule(): void
    {
        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', 'non_existing_module');

        $this->assertEquals(
            [],
            $this->invokePrivateMethod($controller, 'getInstanceConfigurations', $this->getRequestApi())
        );
    }

    public function testGetStaticsConfigurations(): void
    {
        $mod_name = 'supportClient';

        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', $mod_name);
        $this->assertEquals(
            CMbArray::flattenArrayKeys((ConfigurationManager::get())->getValuesForModule($mod_name), $mod_name),
            $this->invokePrivateMethod($controller, 'getStaticConfigurations', $this->getRequestApi())
        );
    }

    public function testGetStaticsConfigurationsEmpty(): void
    {
        $mod_name = 'dPpatients';

        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', $mod_name);
        $this->assertEquals(
            [],
            $this->invokePrivateMethod($controller, 'getStaticConfigurations', $this->getRequestApi())
        );
    }

    public function testGetContextualConfigurationForContext(): void
    {
        $mod_name = 'dPstock';

        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', $mod_name);
        $this->setPrivateProperty($controller, 'model', CConfiguration::getModel($mod_name));

        $context = CGroups::loadCurrent();
        $this->assertEquals(
            CMbArray::flattenArrayKeys(
                CConfigurationModelManager::getValues($mod_name, $context->_class, $context->_id),
                $mod_name
            ),
            $this->invokePrivateMethod(
                $controller,
                'getContextualConfigurationForContext',
                $this->getRequestApi(),
                $context
            )
        );
    }

    public function testGetContextualConfigurationForNonExistingContext(): void
    {
        $mod_name = 'dPpatients';

        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', $mod_name);
        $this->setPrivateProperty($controller, 'model', CConfiguration::getModel($mod_name));

        $context = new CPatient();

        $this->assertEquals(
            [],
            $this->invokePrivateMethod(
                $controller,
                'getContextualConfigurationForContext',
                $this->getRequestApi(),
                $context
            )
        );
    }

    /**
     * @dataProvider isSubContextProvider
     */
    public function testIsSubContext(string $ctx, bool $result): void
    {
        $controller = new ConfigurationController();
        $this->assertEquals($result, $this->invokePrivateMethod($controller, 'isSubContext', $ctx));
    }

    public function testBuildOtherContextualConfigurations(): void
    {
        if (!CModule::getActive('oxSupport') || !CModule::getActive('oxERP')) {
            $this->markTestSkipped('Module OxSupport and oxERP must be installed');
        }

        $equipes = $this->buildEquipes();

        $mod_name = 'oxSupport';

        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', $mod_name);
        $this->setPrivateProperty($controller, 'model', CConfiguration::getModel($mod_name));
        $this->setPrivateProperty(
            $controller,
            'available_contexts',
            ['COXEquipe', 'CGroups', 'CService CGroups.group_id']
        );

        $configurations = $this->invokePrivateMethod(
            $controller,
            'buildOtherContextualConfigurations',
            $this->getRequestApi()
        );

        $this->assertArrayHasKey('COXEquipe', $configurations);
        foreach ($equipes as $_equipe) {
            $this->assertArrayHasKey($_equipe->_id, $configurations['COXEquipe']);
        }

        $this->assertArrayNotHasKey('CGroups', $configurations);
        $this->assertArrayNotHasKey('CService', $configurations);
    }

    /**
     * @runInSeparateProcess
     */
    public function testAddContextualConfigurationsForSubContexts(): void
    {
        $mod_name = 'dPpatients';

        $controller = new ConfigurationController();
        $this->setPrivateProperty($controller, 'mod_name', $mod_name);
        $this->setPrivateProperty($controller, 'model', CConfiguration::getModel($mod_name));

        $user        = CMediusers::get();
        $group       = CGroups::loadCurrent();
        $services    = $this->getServices($group);
        $bad_service = array_pop($services);

        CPermObject::$users_cache[$user->_id]['CService'][$bad_service->_id] = 0;

        $configurations = $this->invokePrivateMethod(
            $controller,
            'addContextualConfigurationsForSubContexts',
            $this->getRequestApi(),
            $group,
            'CService CGroups.group_id',
            []
        );

        $this->assertArrayHasKey('CService', $configurations);
        foreach ($services as $service) {
            $this->assertArrayHasKey($service->_id, $configurations['CService']);
        }

        $this->assertArrayNotHasKey($bad_service->_id, $configurations['CService']);
    }

    public function isSubContextProvider(): array
    {
        return [
            ['CGroups', false],
            ['CService CGroups.group_id', true],
            ['', false],
            ['CBlocOperatoire CGroups.group_id', true],
        ];
    }

    private function buildEquipes(): array
    {
        $function = $this->getObjectFromFixturesReference(CFunctions::class, UsersFixtures::REF_FIXTURES_FUNCTION);

        $equipes = [];
        for ($i = 0; $i < 2; $i++) {
            $equipe              = new COXEquipe();
            $equipe->nom         = uniqid();
            $equipe->function_id = $function->_id;
            if ($msg = $equipe->store()) {
                $this->fail($msg);
            }
        }

        return $equipes;
    }

    private function getServices(CGroups $group): array
    {
        $services = [];
        for ($i = 0; $i < 3; $i++) {
            $services[] = (new CServiceGenerator())->setGroup($group->_id)->setForce(true)->generate();
        }

        return $services;
    }

    private function setPrivateProperty(
        ConfigurationController $controller,
        string $property_name,
        $property_value
    ): void {
        $reflection          = new ReflectionClass($controller);
        $reflextion_property = $reflection->getProperty($property_name);
        $reflextion_property->setAccessible(true);
        $reflextion_property->setValue($controller, $property_value);
    }

    private function getRequestApi(): RequestApi
    {
        return RequestApi::createFromRequest(new Request());
    }
}
