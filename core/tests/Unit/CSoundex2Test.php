<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core\Tests\Unit;

use Ox\Core\CSoundex2;
use Ox\Tests\OxUnitTestCase;

class CSoundex2Test extends OxUnitTestCase {


  public function testBuild() {
    $sound = new CSoundex2();
    $str1  = $sound->build("montaubin");
    $str2  = $sound->build("mintoubin");
    $this->assertEquals($str1, $str2);
  }
}
