<?php

/**
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\dPcim10\Tests\Unit;

use Ox\Mediboard\Cim10\Atih\CCIM10CategoryATIH;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Cim10\Gm\CCategoryCIM10GM;
use Ox\Mediboard\Cim10\Oms\CCodeCIM10OMS;
use Ox\Mediboard\Cim10\Tests\Fixtures\CIM10Fixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

class CCodeCIM10Test extends OxUnitTestCase
{
    /**
     * @config cim10 cim10_version atih
     * @return void
     */
    public function testGetChaptersAtih(): void
    {
        $chapters_atih = CCodeCIM10::getChapters();
        $this->assertContainsOnlyInstancesOf(CCIM10CategoryATIH::class, $chapters_atih);
    }

    /**
     * @config cim10 cim10_version gm
     * @return void
     */
    public function testGetChaptersGM(): void
    {
        $chapters_gm = CCodeCIM10::getChapters();
        $this->assertContainsOnlyInstancesOf(CCategoryCIM10GM::class, $chapters_gm);
    }

    /**
     * @config cim10 cim10_version oms
     * @return void
     */
    public function testGetChaptersOMS(): void
    {
        $chapters_oms = CCodeCIM10::getChapters();
        $this->assertContainsOnlyInstancesOf(CCodeCIM10OMS::class, $chapters_oms);
    }

    /**
     * @config cim10 cim10_version atih
     * @return void
     */
    public function testgetIdFieldAtih(): void
    {
        $id_field_atih = CCodeCIM10::getIdField();
        $this->assertEquals("id", $id_field_atih);
    }

    /**
     * @config cim10 cim10_version gm
     * @return void
     */
    public function testgetIdFieldGM(): void
    {
        $id_field_gm = CCodeCIM10::getIdField();
        $this->assertEquals("id", $id_field_gm);
    }

    /**
     * @config cim10 cim10_version oms
     * @return void
     */
    public function testgetIdFieldOMS(): void
    {
        $id_field_oms = CCodeCIM10::getIdField();
        $this->assertEquals("sid", $id_field_oms);
    }

    public function testAddPoint(): void
    {
        $result = CCodeCIM10::addPoint("A000+587", false);
        $this->assertEquals("A00.0", $result);
    }

    /**
     * @config cim10 cim10_version atih
     * @return void
     */
    public function testgetSubCodesAtih(): void
    {
        $expected       = [
            [
                "code" => "A000",
                "text" => "Choléra à Vibrio cholerae 01, biovar cholerae",
            ],
            [
                "code" => "A001",
                "text" => "Choléra à Vibrio cholerae 01, biovar El Tor",
            ]
            ,
            [
                "code" => "A009",
                "text" => "Choléra, sans précision",
            ],
        ];
        $sub_codes_atih = CCodeCIM10::getSubCodes("A00");
        $this->assertEquals($expected, $sub_codes_atih);
    }

    /**
     * @config cim10 cim10_version gm
     * @return void
     */
    public function testgetSubCodesGM(): void
    {
        $expected     = [
            [
                "code" => "A000",
                "text" => "A Vibrio cholerae 01, biovar cholerae",
            ],
            [
                "code" => "A001",
                "text" => "A Vibrio cholerae 01, biovar El Tor",
            ]
            ,
            [
                "code" => "A009",
                "text" => "Choléra, sans précision",
            ],
        ];
        $sub_codes_gm = CCodeCIM10::getSubCodes("A00");
        $this->assertEquals($expected, $sub_codes_gm);
    }

    /**
     * @config cim10 cim10_version oms
     * @return void
     */
    public function testgetSubCodesOMS(): void
    {
        $sub_codes_oms = CCodeCIM10::getSubCodes("A00");
        $expected      = [
            [
                "code" => "A000",
                "text" => "à Vibrio cholerae 01, biovar cholerae",
            ],
            [
                "code" => "A001",
                "text" => "à Vibrio cholerae 01, biovar El Tor",
            ]
            ,
            [
                "code" => "A009",
                "text" => "choléra, sans précision",
            ],
        ];
        $this->assertEquals($expected, $sub_codes_oms);
    }

    /**
     * @config cim10 cim10_version oms
     * @throws TestsException
     */
    public function testGetUsedCodesFor(): void
    {
        $result = CCodeCIM10::getUsedCodesFor(
            $this->getObjectFromFixturesReference(CMediusers::class, CIM10Fixtures::USER_CIM10)
        );

        $this->assertCount(1, $result);
        $this->assertObjectEquals(CCodeCIM10::get("A00", CCodeCIM10::FULL), $result[0]);
    }

    /**
     * @config cim10 cim10_version atih
     */
    public function testGetByCodeAtih(): void
    {
        $this->assertInstanceOf(CCIM10CategoryATIH::class, CCodeCIM10::get("(A00-A09)"));
    }

    /**
     * @config cim10 cim10_version gm
     */
    public function testGetByCodeGm(): void
    {
        $this->assertInstanceOf(CCategoryCIM10GM::class, CCodeCIM10::get("(A00-A09)"));
    }
}
