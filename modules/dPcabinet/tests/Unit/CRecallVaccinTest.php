<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use Ox\Core\CAppUI;
use Ox\Mediboard\Cabinet\Vaccination\CRecallVaccin;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CRecallVaccinTest
 */
class CRecallVaccinTest extends OxUnitTestCase {

  /**
   * Tests the constructor
   */
  public function test__construct() {
    $base = new CRecallVaccin(2, 3);
    $this->assertEquals(2, $base->age_recall);
    $this->assertEquals(3, $base->age_max);

    $full_construct = new CRecallVaccin(2, 3, 5, 2, false, true);
    $this->assertEquals(2, $full_construct->age_recall);
    $this->assertEquals(3, $full_construct->age_max);
    $this->assertEquals(5, $full_construct->repeat);
    $this->assertEquals(2, $full_construct->colspan);
    $this->assertEquals(false, $full_construct->empty);
    $this->assertEquals(true, $full_construct->mandatory);
  }

  /**
   * Tests the recall age as months
   */
  public function testGetRecallAge() {
    $type_one = new CRecallVaccin(1, 6);
    $this->assertEquals(1, $type_one->getRecallAge(), "Do nothing on the age because it's type one (months)");

    $type_two = new CRecallVaccin(2, 10);
    $this->assertEquals(2, $type_two->getRecallAge(), "This time, multiply by twelve because it's type two (years)");
  }


  /**
   * Tests recalls as strings (for display)
   *
   * @dataProvider stringDatesProvider
   */
  public function testGetStringDates($recall, $str) {
    $this->assertEquals($str, $recall->getStringDates());
  }

  public function stringDatesProvider() {
    return array(
      array(new CRecallVaccin(3), "3 ".CAppUI::tr("month")),
      array(new CRecallVaccin(108), "9 ".CAppUI::tr("years")),
      array(new CRecallVaccin(108, 120), "9 - 10 ".CAppUI::tr("years")),
      array(new CRecallVaccin(180, null, 1), "15 ".CAppUI::tr("years")." and +")
    );
  }
}
