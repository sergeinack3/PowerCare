<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Logger\Processor;

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\Logger\Processor\ApplicationProcessor;
use Ox\Tests\OxUnitTestCase;
use stdClass;

class ApplicationProcessorTest extends OxUnitTestCase
{
    public function testInvokeAddExtra(): void
    {
        $object      = new stdClass();
        $object->foo = 'bar';

        $record = [
            'context' => [$object],
        ];

        $record = call_user_func(new ApplicationProcessor(), $record);

        $this->assertArrayHasKey('extra', $record);
        $this->assertEquals(CAppUI::$user->_id, $record['extra']['user_id']);
        $this->assertEquals($_SERVER['SERVER_ADDR'] ?? null, $record['extra']['server_ip']);
        $this->assertEquals(CMbString::truncate(session_id(), 15), $record['extra']['session_id']);
        $this->assertEquals(CApp::getRequestUID(), $record['extra']['request_uid']);

        $this->assertEquals([['stdClass' => ['foo' => 'bar']]], $record['context']);
    }
}
