<?php
/**
 * @package Mediboard\Admin\Tests
 * @author  SARL OpenXtrem <dev@openxtrem.com>
 * @license GNU General Public License, see http://www.gnu.org/licenses/gpl.html
 */

namespace Ox\Mediboard\Admin\Rgpd\Tests\Unit;

use Ox\Mediboard\Admin\Rgpd\CRGPDException;
use Ox\Mediboard\Admin\Rgpd\CRGPDManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Tests\TestsException;
use Ox\Tests\OxUnitTestCase;

/**
 * Class CRGPDManagerTest
 */
class CRGPDManagerTest extends OxUnitTestCase {

  /**
   *
   * @throws CRGPDException
   * @throws TestsException
   */
  public function testConstruct() {
    $group   = CGroups::loadCurrent();
    $manager = new CRGPDManager($group->_id);
    $this->assertInstanceOf(CRGPDManager::class, $manager);
  }

  /**
   *
   * @throws CRGPDException
   */
  public function testConstructFailed() {
    $this->expectException(CRGPDException::class);
    new CRGPDManager(null);
  }
}
