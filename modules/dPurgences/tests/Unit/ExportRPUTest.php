<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Urgences\Tests\Unit;

use Exception;
use Ox\Core\CMbDT;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Urgences\ExportRPU;
use Ox\Tests\OxUnitTestCase;

class ExportRPUTest extends OxUnitTestCase
{
    /** @var CExchangeSource */
    private $source;
    /** @var CRPU */
    private $rpu;

    /**
     * @inheritDoc
     */
    public function setUp(): void
    {
        $source           = new CExchangeSource();
        $source->host     = "http://www.test.test/";
        $source->password = "test";
        $this->source     = $source;

        $this->rpu = new CRPU();
    }

    /**
     * Test exception the File sending via the configured source
     */
    public function testSendFileException(): void
    {
        ExportRPU::getSource($this->source);
        $this->expectException(Exception::class);
        ExportRPU::sendFile("", "test");
    }

    /**
     * Test to return the datetime value is null
     */
    public function testCheckDatetimeIsNULL(): void
    {
        $result = ExportRPU::checkDatetime();
        $this->assertNull($result);
    }

    /**
     * Test to return the datetime value is not empty
     */
    public function testCheckDatetime(): void
    {
        $datetime        = CMbDT::dateTime();
        $format_datetime = CMbDT::transform($datetime, null, "%d/%m/%Y %H:%M");
        $result          = ExportRPU::checkDatetime($datetime);
        $this->assertEquals($result, $format_datetime);
    }

    /**
     * Test the counter RPU attentes by type (value 0)
     */
    public function testCounterRpuAttentesNoValues(): void
    {
        $counter_rpu_attentes = ExportRPU::counterRpuAttentes($this->rpu);

        foreach ($counter_rpu_attentes as $_counter_rpu_attente) {
            $this->assertEquals(0, $_counter_rpu_attente);
        }
    }
}
