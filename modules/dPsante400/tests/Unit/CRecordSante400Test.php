<?php

/**
 * @package Mediboard\OxCabinet\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sante400\Tests\Unit;

use Exception;
use Ox\Mediboard\Sante400\CRecordSante400;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CRecordSante400Test
 */
class CRecordSante400Test extends OxUnitTestCase
{
    public function testConsumeExceptionNotArray(): void
    {
        $this->expectException(Exception::class);

        $record = new CRecordSante400();
        $record->consume('key');
    }

    public function testConsumeExceptionKeyNotFound(): void
    {
        $this->expectException(Exception::class);

        $record       = new CRecordSante400();
        $record->data = ['key' => 'value'];
        $record->consume('key2');
    }

    public function testConsumeOK(): void
    {
        $record       = new CRecordSante400();
        $record->data = ['key' => 'value'];
        $this->assertEquals('value', $record->consume('key'));
    }
}
