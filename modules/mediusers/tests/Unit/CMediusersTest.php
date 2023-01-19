<?php
/**
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Mediusers\Tests\Unit;

use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CMediusersTest extends OxUnitTestCase {
  /**
   * @param string $cp CP to test
   *
   * @config dPpatients INSEE france 1
   * @config dPpatients INSEE suisse 1
   * @config dPpatients INSEE allemagne 1
   * @config dPpatients INSEE espagne 1
   * @config dPpatients INSEE portugal 1
   * @config dPpatients INSEE gb 1
   *
   * @dataProvider cpProvider
   */
  public function testCPSize($cp) {
    $cp_fields = ['_user_cp'];

    $mediusers = new CMediusers();
    foreach ($cp_fields as $_cp) {
      $mediusers->{$_cp} = $cp;
    }
    $mediusers->repair();

    foreach ($cp_fields as $_cp) {
      $this->assertEquals($cp, $mediusers->{$_cp});
    }
  }

  public function cpProvider() {
    return array(
      ["3750-012"], ["12"], ["17000"], ["6534887"]
    );
  }
}
