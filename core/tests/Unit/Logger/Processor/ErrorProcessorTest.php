<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Test\Unit\Logger\Processor;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CMbString;
use Ox\Core\Logger\Processor\ErrorProcessor;
use Ox\Tests\OxUnitTestCase;

class ErrorProcessorTest extends OxUnitTestCase
{
    public function testGenerateSignatureHash(): void
    {
        $processor = new ErrorProcessor();
        $hash1     = $this->invokePrivateMethod($processor, 'generateSignatureHash', 'test1234', 'type', 'file', 10);
        $hash2     = $this->invokePrivateMethod($processor, 'generateSignatureHash', 'test56789', 'type', 'file', 10);
        $hash3     = $this->invokePrivateMethod($processor, 'generateSignatureHash', 'test1234', 'type2', 'file', 10);

        $this->assertEquals($hash1, $hash2);
        $this->assertNotEquals($hash1, $hash3);
    }

    public function testInvokeWithoutException(): void
    {
        $record = ['foo' => 'bar'];
        $record = call_user_func(new ErrorProcessor(), $record);
        $this->assertEquals(['foo' => 'bar'], $record);
    }

    public function testInvokeAddExtraData(): void
    {
        $record = ['context' => ['exception' => new Exception('Message', 10)]];
        $record = call_user_func(new ErrorProcessor(), $record);

        $this->assertArrayHasKey('extra', $record);
        $this->assertEquals(CAppUI::$user->_id, $record['extra']['user_id']);
        $this->assertEquals($_SERVER['SERVER_ADDR'] ?? null, $record['extra']['server_ip']);
        $this->assertEquals(CMbString::truncate(session_id(), 15), $record['extra']['session_id']);
        $this->assertArrayHasKey('microtime', $record['extra']);
        $this->assertEquals(CApp::getRequestUID(), $record['extra']['request_uuid']);
        $this->assertEquals('exception', $record['extra']['type']);
        $this->assertArrayHasKey('file', $record['extra']);
        $this->assertArrayHasKey('signature_hash', $record['extra']);
        $this->assertArrayHasKey('data', $record['extra']);
        $this->assertArrayHasKey('stacktrace', $record['extra']['data']);
        $this->assertArrayHasKey('param_GET', $record['extra']['data']);
        $this->assertArrayHasKey('param_POST', $record['extra']['data']);
        $this->assertArrayHasKey('session_data', $record['extra']['data']);
        $this->assertEquals(1, $record['extra']['count']);
    }
}
