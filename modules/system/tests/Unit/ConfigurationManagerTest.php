<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests;

use Ox\Mediboard\System\ConfigurationManager;
use Ox\Tests\OxUnitTestCase;

class ConfigurationManagerTest extends OxUnitTestCase {
  public function testGetInstance() {
    $manager = ConfigurationManager::get();
    $this->assertInstanceOf(ConfigurationManager::class, $manager);

    $same_manager = ConfigurationManager::get();
    $this->assertSame($manager, $same_manager);
  }
}
