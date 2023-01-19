<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Module\Requirements;

use Ox\Core\Module\Requirements\CRequirementsDescription;
use Ox\Core\Module\Requirements\CRequirementsManager;

/**
 * Class CRequirementsDummy
 */
class CRequirementsDummy extends CRequirementsManager {

  /**
   * @tab test_tab
   * @group test_group
   *
   * @return void
   */
  public function checkFunction() {
    $this->assertTrue(true, 'checkFunction2 - assertTrue');
  }

  /**
   * @return void
   */
  public function checkFunction2() {
    $this->assertFalse(true, 'checkFunction2 - assertFalse');
  }

  /**
   * @tab test_tab3
   *
   * @return void
   */
  public function checkFunction3() {
    $this->assertFalse(true, 'checkFunction3 - assertFalse');
    $this->assertTrue(true, 'checkFunction3 - assertTrue');
  }

  /**
   * @group test_tab4
   *
   * @return void
   */
  public function checkFunction4() {
    $this->assertFalse(false, 'checkFunction4 - assertFalse');
  }

  /**
   * @return CRequirementsDescription
   */
  public function getDescription(): CRequirementsDescription {
    $description = parent::getDescription();
    $description->addTitle("title");
    $description->addLine();
    $description->addDescription("test");
    $description->addDescriptionList("test list");
    $description->addDescriptionList("test list 2");

    return $description;
  }
}