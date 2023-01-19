<?php
/**
 * @package Mediboard\Test
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\Config\CConfigDist;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CConfigDistTest
 */
class CConfigDistTest extends OxUnitTestCase {

  /**
   * @return void
   */
  public function testBuild() {
    $config_dist = new CConfigDist();
    $msg         = $config_dist->build();
    $this->assertStringStartsWith('Generated config_dist file', $msg);
    $path = dirname(__DIR__, 3) . '/includes/config_dist.php';
    $this->assertFileExists($path);
  }
}
