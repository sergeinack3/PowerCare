<?php

/**
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Tests\Unit\Domain\CarePath;

use Ox\Mediboard\Jfse\Domain\CarePath\CarePathDoctor;
use Ox\Mediboard\Jfse\Exceptions\CarePath\CarePathException;
use Ox\Mediboard\Jfse\Exceptions\CarePath\CarePathMappingException;
use Ox\Mediboard\Jfse\Tests\Unit\UnitTestJfse;

class CarePathDoctorTest extends UnitTestJfse
{
    /** @var CarePathDoctor */
    private $doctor;

    public function setUp(): void
    {
        parent::setUp();

        $this->doctor = CarePathDoctor::hydrate(
            ["invoicing_id" => "123456789", "first_name" => "John", "last_name" => "Doe"]
        );
    }

    public function testGetFirstName(): void
    {
        $this->assertEquals("John", $this->doctor->getFirstName());
    }

    public function testGetLastName(): void
    {
        $this->assertEquals("Doe", $this->doctor->getLastName());
    }

    public function testGetFinessId(): void
    {
        $this->assertEquals("123456789", $this->doctor->getInvoicingId());
    }

    public function testExpectExceptionWhenFinessIdIsNotNumeric(): void
    {
        $this->expectException(CarePathMappingException::class);
        CarePathDoctor::hydrate(["invoicing_id" => "A1B2C3"]);
    }

    public function testExpectExceptionWhenFinessIdIsNotTheRightSize(): void
    {
        $this->expectException(CarePathMappingException::class);
        CarePathDoctor::hydrate(["invoicing_id" => "111"]);
    }
}
