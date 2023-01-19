<?php
/**
 * @package Mediboard\Maternite\Tests\Unit
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Maternite\Tests\Unit;

use Ox\Core\CMbDT;
use Ox\Mediboard\Maternite\CGrossesse;
use Ox\Mediboard\Maternite\CNaissance;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\OxUnitTestCase;

class CNaissanceTest extends OxUnitTestCase {

  public function test__construct() {
    $naissance = new CNaissance();
    $this->assertInstanceOf(CNaissance::class, $naissance);
  }

  public function testUpdateFormFields() {
    $dnow = CMbDT::date();
    $tnow = CMbDT::time();
    $naissance = new CNaissance();

    $naissance->date_time = "$dnow $tnow";
    $naissance->apgar_coeur_1 = $naissance->apgar_respi_1 =
    $naissance->apgar_tonus_1 = $naissance->apgar_reflexes_1 = $naissance->apgar_coloration_1 = 1;
    $naissance->apgar_coeur_3 = $naissance->apgar_respi_3 =
    $naissance->apgar_tonus_3 = $naissance->apgar_reflexes_3 = $naissance->apgar_coloration_3 = 1;
    $naissance->apgar_coeur_5 = $naissance->apgar_respi_5 =
    $naissance->apgar_tonus_5 = $naissance->apgar_reflexes_5 = $naissance->apgar_coloration_5 = 1;
    $naissance->apgar_coeur_10 = $naissance->apgar_respi_10 =
    $naissance->apgar_tonus_10 = $naissance->apgar_reflexes_10 = $naissance->apgar_coloration_10 = 1;

    $naissance->updateFormFields();

    $this->assertEquals($tnow, $naissance->_heure);
    $this->assertEquals(5, $naissance->_apgar_1);
    $this->assertEquals(5, $naissance->_apgar_3);
    $this->assertEquals(5, $naissance->_apgar_5);
    $this->assertEquals(5, $naissance->_apgar_10);
  }

  public function testLoadRefOperation() {
    $naissance = new CNaissance();
    $this->assertInstanceOf(COperation::class, $naissance->loadRefOperation());
  }

  public function testLoadRefGrossesse() {
    $naissance = new CNaissance();
    $this->assertInstanceOf(CGrossesse::class, $naissance->loadRefGrossesse());
  }

  public function testLoadRefSejourEnfant() {
    $naissance = new CNaissance();
    $this->assertInstanceOf(CSejour::class, $naissance->loadRefSejourEnfant());
  }

  public function testLoadRefSejourMaman() {
    $naissance = new CNaissance();
    $this->assertInstanceOf(CSejour::class, $naissance->loadRefSejourMaman());
  }

  public function testGetNumNais() {
    $naissance = new CNaissance();
    $naissance->date_time = CMbDT::dateTime();
    $this->assertIsInt($naissance->getNumNaissance());
  }
}
