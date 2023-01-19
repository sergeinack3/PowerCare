<?php
/**
 * @package Tests
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


namespace Ox\Core\Tests\Unit;

use Ox\Core\CNuts;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CNutsTest extends OxUnitTestCase {

  public function testConvert() {
    $nuts  = new CNuts("123456789", "");
    $text1 = $nuts->convert("fr-FR");
    $text2 = "cent vingt-trois millions quatre cent cinquante-six mille sept cent quatre-vingt-neuf";
    $this->assertEquals($text1, $text2);
  }

  public function testUnit() {
    $nuts  = new CNuts("12,45", "EUR");
    $text1 = $nuts->convert("fr-FR");
    $text2 = "douze euros, quarante-cinq centimes";
    $this->assertEquals($text1, $text2);
  }
}
