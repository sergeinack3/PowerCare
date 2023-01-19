<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Controllers;

use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CMbString;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\Controllers\ModulesController;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Response;

class ModulesControllerTest extends OxUnitTestCase
{
    public function testGetModuleList(): void
    {
        $controller = new ModulesController();
        foreach ([ModulesController::STATE_INSTALLED,ModulesController::STATE_ACTIVE,ModulesController::STATE_VISIBLE] as $state) {
            $modules = $this->invokePrivateMethod($controller, 'getModuleList', $state);
            foreach ($modules as $module) {
                $function = 'get' . CMbString::capitalize($state);
                $this->assertNotNull(CModule::$function($module->mod_name));
            }
        }
    }

    public function testGetModuleListInvalidState(): void
    {
        $controller = new ModulesController();

        $this->expectExceptionObject(
            new HttpException(
                Response::HTTP_NOT_FOUND,
                "State 'toto' is not in " . implode(', ', ModulesController::AVAILABLE_STATES)
            )
        );
        $this->invokePrivateMethod($controller, 'getModuleList', 'toto');
    }
}
