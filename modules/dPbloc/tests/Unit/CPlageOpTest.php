<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc\Tests;

use Ox\Core\CMbDT;
use Ox\Mediboard\Bloc\CBlocage;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Tests\OxUnitTestCase;

class CPlageOpTest extends OxUnitTestCase {

  public function test__construct() {
    $plage_op = new CPlageOp();
    $this->assertInstanceOf(CPlageOp::class, $plage_op);
  }

  public function testGuessHoraireVoulu() {
    $function = new CFunctions();
    $tmp = $function->loadList(null, null, 1);
    $function = reset($tmp);

    $plage_op = new CPlageOp();
    $plage_op->spec_id = $function->_id;
    $plage_op->unique_chir = false;
    $horaire = $plage_op->guessHoraireVoulu();

    $this->assertFalse($horaire);
  }

  /**
   * @group schedules
   * @throws \Exception
   */
  public function testCheckBlocageSalle() {
    $plage_op = new CPlageOp();
    $test_blocage = $plage_op->checkBlocageSalle();
    $this->assertFalse($test_blocage);

    $bloc = new CBlocOperatoire();
    $tmp = $bloc->loadList(null, null, 1);
    $bloc = reset($tmp);

    $salle = new CSalle();
    $salle->bloc_id = $bloc->_id;
    $salle->nom = "Salle test";
    $salle->store();

    $blocage = new CBlocage();
    $blocage->salle_id = $salle->_id;
    $blocage->deb = "2019-10-10 07:00:00";
    $blocage->fin = "2019-10-10 11:00:00";
    $blocage->store();

    $plage_op->salle_id = $salle->_id;
    $plage_op->date  = "2019-10-10";
    $plage_op->debut = "06:00:00";
    $plage_op->fin   = "19:00:00";

    $test_blocage = $plage_op->checkBlocageSalle();
    $this->assertFalse($test_blocage);

    $blocage->fin = "2019-10-10 22:00:00";
    $blocage->store();

    $test_blocage = $plage_op->checkBlocageSalle();
    $this->assertTrue($test_blocage);
  }
}
