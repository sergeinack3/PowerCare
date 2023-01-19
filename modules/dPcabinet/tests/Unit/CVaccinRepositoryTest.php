<?php

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Core\Cache;
use Ox\Mediboard\Cabinet\Vaccination\CRecallVaccin;
use Ox\Mediboard\Cabinet\Vaccination\CVaccin;
use Ox\Mediboard\Cabinet\Vaccination\CVaccinRepository;
use Ox\Tests\OxUnitTestCase;

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */
class CVaccinRepositoryTest extends OxUnitTestCase
{
    private $mock;

    public function setUp(): void
    {
        $vaccines_cache = new Cache("vaccination", "vaccines", Cache::NONE);
        $recalls_cache  = new Cache("vaccination", "recalls", Cache::NONE);

        // Create a stub for the SomeClass class.
        $this->mock = $this->getMockBuilder(CVaccinRepository::class)
            ->setConstructorArgs([$vaccines_cache, $recalls_cache])
            ->setMethods(['loadVaccineJson'])
            ->getMock();

        $vaccine = [
            "columns"  => ["1 mois", "2 mois", "3 mois"],
            "vaccines" => [
                "VACCS1" => [
                    "shortname" => "VACCS",
                    "longname"  => "VACCINE 1",
                    "color"     => "#ccc",
                    "recall"    => [
                        ["age" => 2, "mandatory" => true],
                        ["age" => 3],
                    ],
                ],
            ],
        ];

        // Configure the stub.
        $this->mock->method('loadVaccineJson')
            ->willReturn($vaccine);

        parent::setUp();
    }

    public function testGetAll()
    {
        $value = $this->mock->getAll();

        $r1       = new CRecallVaccin(2, null, null, 1, false, true);
        $r2       = new CRecallVaccin(3);
        $expected = new CVaccin("VACCS1", "VACCS", "VACCINE 1", "#ccc", [$r1, $r2]);

        $this->assertEquals([$expected], $value);
    }

    public function testFindByType()
    {
        $this->assertNull($this->mock->findByType("VACCS2"));
        $r1       = new CRecallVaccin(2, null, null, 1, false, true);
        $r2       = new CRecallVaccin(3);
        $expected = new CVaccin("VACCS1", "VACCS", "VACCINE 1", "#ccc", [$r1, $r2]);

        $this->assertEquals($expected, $this->mock->findByType("VACCS1"));
    }

    public function testGetColorsPerType()
    {
        $this->assertEquals(["VACCS1" => "#ccc"], $this->mock->getColorsPerType());
    }

    public function testGetDates()
    {
        $this->assertEquals(["1 mois", "2 mois", "3 mois"], $this->mock->getDates());
    }

    public function testGetRecalls()
    {
        $r1 = new CRecallVaccin(2, null, null, 1, false, true);
        $r2 = new CRecallVaccin(3);
        $this->assertEquals([$r1, $r2], $this->mock->getRecalls());
    }

    public function testGetAvailableTypesRecall()
    {
        // keys == age recalls
        $this->assertEquals(["2" => ["VACCS1"], "3" => ["VACCS1"]], $this->mock->getAvailableTypesRecall());
    }
}
