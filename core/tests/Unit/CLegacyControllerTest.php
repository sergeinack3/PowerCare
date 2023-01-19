<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Exception;
use Ox\Core\CLegacyController;
use Ox\Core\CStoredObject;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Util\Json;
use stdClass;

class CLegacyControllerTest extends OxUnitTestCase
{
    /**
     * @param mixed $data
     *
     * @dataProvider renderJsonExceptionProvider
     * @throws TestsException
     */
    public function testRenderJsonExpectExceptions($data): void
    {
        $this->expectException(Exception::class);
        $this->invokePrivateMethod(CLegacyController::class, "renderJson", $data);
    }

    public function renderJsonExceptionProvider(): array
    {
        return [[new StdClass()], [null]];
    }
}
