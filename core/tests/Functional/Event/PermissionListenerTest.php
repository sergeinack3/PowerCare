<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Functional\Event;

use Ox\Core\Kernel\Event\PermissionListener;
use Ox\Core\Kernel\Exception\PermissionException;
use Ox\Core\Module\CModule;
use Ox\Mediboard\System\Controllers\SystemController;
use Ox\Tests\OxWebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Test the PermissionListener.
 * - onController for public route check if the module is active and not obsolete (obsolete is not testable yet)
 * - onController for non public route check if the user have the perm on the module -> not testable yet
 */
class PermissionListenerTest extends OxWebTestCase
{
    /**
     * @return void
     * @runInSeparateProcess
     */
    public function testOnControllerForPublicRouteWithModuleNotActive(): void
    {
        $kernel = static::createKernel();
        $event = new ControllerEvent(
            $kernel,
            [new SystemController(), 'status'],
            new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        unset(CModule::$active['system']);

        $this->expectExceptionObject(
            new PermissionException(
                Response::HTTP_FORBIDDEN,
                "The module system is not enabled.",
                [],
                0
            )
        );

        (new PermissionListener())->onController($event);
    }
}
