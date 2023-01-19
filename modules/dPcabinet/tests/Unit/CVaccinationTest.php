<?php

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CVaccin;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Tests\OxUnitTestCase;
use PHPUnit\Framework\TestCase;

/**
 * Class CVaccinationTest
 */
class CVaccinationTest extends OxUnitTestCase {

  /**
   * Test if it can find a type of vaccine
   */
  public function testIsVaccinationActive() {
    $vaccines = [
      new CVaccin("VACCS1"),
      new CVaccin("VACCS2"),
      new CVaccin("Autre")
    ];

    $this->assertFalse(CVaccination::isVaccinationActive(new CVaccin("VACCS3"), $vaccines));
    $this->assertTrue(CVaccination::isVaccinationActive(new CVaccin("VACCS1"), $vaccines));
  }

  /**
   * Test the magical to string method
   */
  public function test__toString() {
    $vaccine = new CVaccin("VACCS1", "One", "Vaccs One");
    $vaccine2 = new CVaccin("Autre", "Other", "Other vaccs");

    $injection = new CInjection();
    $injection->speciality = "VACCSINATOR";

    $vaccination = new CVaccination();
    $vaccination->_ref_injection = $injection;

    $vaccination->_ref_vaccine = $vaccine;
    $this->assertEquals("Vaccs One<br>Produit: ".$injection->speciality, $vaccination->__toString());

    $vaccination->_ref_vaccine = $vaccine2;
    $this->assertEquals("Produit: ".$injection->speciality, $vaccination->__toString());
  }
}
