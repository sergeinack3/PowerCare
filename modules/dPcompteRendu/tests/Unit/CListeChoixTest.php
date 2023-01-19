<?php
/**
 * @package Mediboard\CompteRendu\Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\CompteRendu\Tests\Unit;

use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\CompteRendu\CListeChoix;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

class CListeChoixTest extends OxUnitTestCase {

  public function test__construct() {
    $liste = new CListeChoix();
    $this->assertInstanceOf(CListeChoix::class, $liste);
  }

  public function test_loadRefUser() {
    $liste = new CListeChoix();
    $this->assertInstanceOf(CMediusers::class, $liste->loadRefUser());
  }

  public function test_loadRefFunction() {
    $liste = new CListeChoix();
    $this->assertInstanceOf(CFunctions::class, $liste->loadRefFunction());
  }

  public function test_loadRefGroup() {
    $liste = new CListeChoix();
    $this->assertInstanceOf(CGroups::class, $liste->loadRefGroup());
  }

  public function test_loadRefModele() {
    $liste = new CListeChoix();
    $this->assertInstanceOf(CCompteRendu::class, $liste->loadRefModele());
  }

  public function test_orderItems() {
    $liste = new CListeChoix();
    $valeurs = array("b", "a");

    $liste->orderItems($valeurs);
    $this->assertEquals(array("a", "b"), $valeurs);
  }
}
