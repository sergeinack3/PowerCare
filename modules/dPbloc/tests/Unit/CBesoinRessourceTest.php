<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Bloc\Tests;

use Ox\Mediboard\Bloc\CBesoinRessource;
use Ox\Mediboard\Bloc\CTypeRessource;
use Ox\Mediboard\Bloc\CUsageRessource;
use Ox\Mediboard\PlanningOp\CProtocole;
use Ox\Tests\OxUnitTestCase;

class CBesoinRessourceTest extends OxUnitTestCase {
  public function test__construct() {
    $besoin = new CBesoinRessource();
    $this->assertInstanceOf(CBesoinRessource::class, $besoin);
  }

  public function testIsAvailable() {
    $besoin = new CBesoinRessource();
    $this->assertIsBool($besoin->isAvailable());
  }

  public function testLoadRefTypeRessource() {
    $besoin = new CBesoinRessource();
    $this->assertInstanceOf(CTypeRessource::class, $besoin->loadRefTypeRessource());
  }

  public function testLoadRefProtocole() {
    $besoin = new CBesoinRessource();
    $this->assertInstanceOf(CProtocole::class, $besoin->loadRefProtocole());
  }

  public function loadRefUsage() {
    $besoin = new CBesoinRessource();
    $this->assertInstanceOf(CUsageRessource::class, $besoin->loadRefUsage());
  }
}
