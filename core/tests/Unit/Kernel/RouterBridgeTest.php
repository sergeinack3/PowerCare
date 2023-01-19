<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\Kernel\Routing\RouterBridge;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Routing\Router;

class RouterBridgeTest extends OxUnitTestCase
{
    public function testSingelton()
    {
        $instance = RouterBridge::getInstance();
        $this->assertEquals($instance, RouterBridge::getInstance());
        $this->assertInstanceOf(Router::class, $instance);
    }

    public function testOptionsIsSet()
    {
        $instance = RouterBridge::getInstance();
        $this->assertNotNull($instance->getOption('cache_dir'));
    }
}
