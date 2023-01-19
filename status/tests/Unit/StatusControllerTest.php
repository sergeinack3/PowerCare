<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Status\Tests\Unit;

use Ox\Status\Controllers\StatusController;
use Ox\Tests\OxUnitTestCase;

class StatusControllerTest extends OxUnitTestCase
{
    public function testConstruct(): void
    {
        $controller = new StatusController();
        $this->assertInstanceOf(StatusController::class, $controller);
    }
}
