<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Tests\Unit;

use DateTime;
use Exception;
use Ox\Mediboard\Cabinet\Vaccination\CInjection;
use Ox\Mediboard\Cabinet\Vaccination\CRecallVaccin;
use Ox\Mediboard\Cabinet\Vaccination\CVaccin;
use Ox\Mediboard\Cabinet\Vaccination\CVaccination;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CVaccinationTest
 */
class CInjectionTest extends OxUnitTestCase {

  /**
   * Test to check if a user has a given vaccination
   */
  public function testHasVaccination() {
    // -- First injection
    $injection1             = new CInjection();
    $injection1->recall_age = 1;

    $r1                           = new CRecallVaccin(1);
    $vaccination1                 = new CVaccination();
    $vaccination1->type           = "Autre";
    $vaccination1->_ref_vaccine   = new CVaccin("VACCS1", null, null, null, [$r1]);
    $vaccination1->_ref_injection = $injection1;

    $injection1->_ref_vaccinations = [$vaccination1];

    // -- Second injection
    $injection2             = new CInjection();
    $injection2->recall_age = 2;

    $r2                           = new CRecallVaccin(2);
    $vaccination2                 = new CVaccination();
    $vaccination2->type           = "Autre";
    $vaccination2->_ref_vaccine   = new CVaccin("VACCS2", null, null, null, [$r2]);
    $vaccination2->_ref_injection = $injection2;

    $injection2                    = new CInjection();
    $injection2->_ref_vaccinations = [$vaccination2];

    // -- Make the vaccinations array
    $vaccinations = [$vaccination1, $vaccination2];

    // -- Test it !
    $index = CInjection::hasVaccination($vaccinations, "Other", new CRecallVaccin(1));
    $this->assertFalse($index, "This vaccination shouldn't be valid (bad vaccine type)");

    $index = CInjection::hasVaccination($vaccinations, "Autre", new CRecallVaccin(3));
    $this->assertFalse($index, "This vaccination shouldn't be valid (bad recall)");

    $index = CInjection::hasVaccination($vaccinations, "Autre", new CRecallVaccin(1));
    $this->assertSame($vaccination1, $index, "This vaccination should be valid (vaccine type and recall are identical)");
  }

  /**
   * Tests the array generated for display
   *
   * @depends testHasVaccination
   */
  public function testGenerateArray() {
    $injections = self::vaccinationsData();
    $vaccines   = [
      new CVaccin(
        "BCG",
        "BCG shortname",
        "BCG shortname",
        "#000",
        [new CRecallVaccin(1)]
      ),
      new CVaccin(
        "DTP",
        "DTP shortname",
        "DTP longname",
        "#ff00ff",
        [new CRecallVaccin(216)]
      )];

    $recalls = [new CRecallVaccin(1), new CRecallVaccin(216)];

    $actual_array = CInjection::generateArray($injections, $vaccines, $recalls);

    $bcg = $injections[0];
    $dtp = $injections[1];

    $expected_array = ["BCG" => [1 => $bcg, 216 => null],
                       "DTP" => [1 => null, 216 => $dtp]];

    // The goal here is to test the structure made by the function and not the content
    $this->assertEquals($expected_array, $actual_array, "Recalls aren't equal");
  }

  /**
   * Data to test vaccinations
   *
   * @return array
   * @throws Exception
   */
  public function vaccinationsData() {
    $injection1                    = new CInjection();
    $injection1->patient_id        = 1;
    $injection1->practitioner_name = "Jean-Pierre";
    $injection1->injection_date    = (new DateTime())->format("Y-m-d");
    $injection1->batch             = "A0001BC";
    $injection1->speciality        = "BCBG";
    $injection1->_recall           = new CRecallVaccin(1);
    $injection1->recall_age        = $injection1->_recall->getRecallAge();

    $vaccination1                 = new CVaccination();
    $vaccination1->type           = "BCG";
    $vaccination1->_ref_injection = $injection1;
    $vaccination1->_ref_vaccine   = new CVaccin(
      "BCG",
      "BCG shortname",
      "BCG shortname",
      "#000",
      [new CRecallVaccin(1)]
    );

    $injection1->_ref_vaccinations = [$vaccination1];

    $injection2                    = new CInjection();
    $injection2->patient_id        = 1;
    $injection2->practitioner_name = "Marc";
    $injection2->injection_date    = (new DateTime())->format("Y-m-d");
    $injection2->batch             = "A0002BC";
    $injection2->speciality        = "PolioDt";
    $injection2->_recall           = new CRecallVaccin(216);
    $injection2->recall_age        = $injection2->_recall->getRecallAge();

    $vaccination2                 = new CVaccination();
    $vaccination2->type           = "DTP";
    $vaccination2->_ref_injection = $injection2;
    $vaccination2->_ref_vaccine   = new CVaccin(
      "DTP",
      "DTP shortname",
      "DTP longname",
      "#ff00ff",
      [new CRecallVaccin(216)]
    );

    $injection2->_ref_vaccinations = [$vaccination2];

    $injection3                    = new CInjection();
    $injection3->patient_id        = 1;
    $injection3->practitioner_name = "Maxence";
    $injection3->injection_date    = (new DateTime())->format("Y-m-d");
    $injection3->batch             = "A0003BC";
    $injection3->speciality        = "coquette";
    $injection3->_recall           = new CRecallVaccin(6);
    $injection3->recall_age        = $injection3->_recall->getRecallAge();

    $vaccination3                 = new CVaccination();
    $vaccination3->type           = "coqueluche";
    $vaccination3->_ref_injection = $injection3;
    $vaccination3->_ref_vaccine   = new CVaccin(
      "coqueluche",
      "coqueluche shortname",
      "coqueluche longname",
      "#ccc",
      [new CRecallVaccin(300)]
    );

    $injection3->_ref_vaccinations = [$vaccination3];

    return array($injection1, $injection2, $injection3);
  }
}
