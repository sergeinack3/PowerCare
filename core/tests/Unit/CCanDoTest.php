<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\Kernel\Exception\PublicEnvironmentException;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description
 */
class CCanDoTest extends OxUnitTestCase
{
    public function testPublicEnvironmentPreventsUsage(): void
    {
        $this->markTestSkipped('TODO [public] Restore with public');
        $public_request = new Request();
        $public_request->attributes->set('is_api', true);
        $public_request->attributes->set('security', []);

        CApp::getInstance()->setPublic($public_request);

        $can = new CCanDo();

        $this->expectException(PublicEnvironmentException::class);

        $can->denied();

        $can->needsRead();
        $can->needsEdit();
        $can->needsAdmin();

        $can::check();
        $can::checkRead();
        $can::checkEdit();
        $can::checkAdmin();

        $can::read();
        $can::edit();
        $can::admin();
    }

    /**
     * Preventing unit test handlers to fail when in public environment
     * (because of CApp global state internally modified).
     */
    public function tearDown(): void
    {
        CApp::getInstance()->setPublic(new Request());
    }
}
