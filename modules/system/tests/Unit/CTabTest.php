<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CModuleAction;
use Ox\Mediboard\System\CPinnedTab;
use Ox\Tests\OxUnitTestCase;

/**
 * Test class for pinned tabs
 */
class CTabTest extends OxUnitTestCase
{
    private $current_user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->current_user = CMediusers::get();
    }

    public function testCreatePinOk(): void
    {
        $module        = CModule::getInstalled('system');
        $mod_action_id = CModuleAction::getID('system', 'about');

        $pin            = new CPinnedTab();
        $pin->_tab_name = 'about';
        $pin->_mod_name = 'system';

        $pin->createPin($this->current_user);

        $this->assertEquals($this->current_user->_id, $pin->user_id);
        $this->assertEquals($module->_id, $pin->module_id);
        $this->assertEquals($mod_action_id, $pin->module_action_id);
    }

    public function testCreatePinModuleDoesNotExists(): void
    {
        $mod_name = uniqid();

        $pin            = new CPinnedTab();
        $pin->_mod_name = $mod_name;
        $pin->_tab_name = 'about';

        $this->expectExceptionMessage("system-msg-The {$mod_name} module is not active");
        $pin->createPin($this->current_user);
    }

    public function testCreatePinTabsDoesNotExists(): void
    {
        $pin            = new CPinnedTab();
        $pin->_tab_name = uniqid();
        $pin->_mod_name = 'system';

        $this->expectExceptionMessage('CPinnedTab-Error-Tab is not part of module');
        $pin->createPin($this->current_user);
    }

    public function testRemovePinnedTabs(): void
    {
        $other_user = $this->createPins();

        $mod_system  = CModule::getInstalled('system');
        $mod_patient = CModule::getInstalled('dPpatients');

        $this->assertNotEmpty($mod_system->getPinnedTabs());
        $this->assertNotEmpty($mod_system->getPinnedTabs($other_user));
        $this->assertNotEmpty($mod_patient->getPinnedTabs());
        $this->assertNotEmpty($mod_patient->getPinnedTabs($other_user));

        CPinnedTab::removePinnedTabs('system', $this->current_user);

        $this->assertEmpty($mod_system->getPinnedTabs());
        $this->assertNotEmpty($mod_system->getPinnedTabs($other_user));
        $this->assertNotEmpty($mod_patient->getPinnedTabs());
        $this->assertNotEmpty($mod_patient->getPinnedTabs($other_user));
    }

    public function testRemovePinnedTabsNoModule(): void
    {
        $mod_name = uniqid();

        $this->expectExceptionMessage("system-msg-The {$mod_name} module is not active");
        CPinnedTab::removePinnedTabs($mod_name, $this->current_user);
    }

    private function createPins(): CMediusers
    {
        $other_user = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_LOREM_IPSUM);

        CPinnedTab::removePinnedTabs('system', $other_user);
        CPinnedTab::removePinnedTabs('dPpatients', $other_user);
        CPinnedTab::removePinnedTabs('system', $this->current_user);
        CPinnedTab::removePinnedTabs('dPpatients', $this->current_user);

        $mod_system  = CModule::getInstalled('system');
        $mod_patient = CModule::getInstalled('dPpatients');

        $mod_action_system_1 = CModuleAction::getID('system', 'about');
        $mod_action_system_2 = CModuleAction::getID('system', 'view_modules');

        $mod_action_patient_1 = CModuleAction::getID('dPpatients', 'vw_idx_patients');
        $mod_action_patient_2 = CModuleAction::getID('dPpatients', 'vw_correspondants');


        $this->createPin($mod_system, $mod_action_system_1, $this->current_user);
        $this->createPin($mod_system, $mod_action_system_2, $this->current_user);
        $this->createPin($mod_patient, $mod_action_patient_1, $this->current_user);
        $this->createPin($mod_patient, $mod_action_patient_2, $this->current_user);

        $this->createPin($mod_system, $mod_action_system_1, $other_user);
        $this->createPin($mod_system, $mod_action_system_2, $other_user);
        $this->createPin($mod_patient, $mod_action_patient_1, $other_user);
        $this->createPin($mod_patient, $mod_action_patient_2, $other_user);

        return $other_user;
    }

    private function createPin(CModule $module, int $module_action, CMediusers $user): CPinnedTab
    {
        $pin                   = new CPinnedTab();
        $pin->user_id          = $user->_id;
        $pin->module_id        = $module->_id;
        $pin->module_action_id = $module_action;

        if ($msg = $pin->store()) {
            $this->fail($msg);
        }

        return $pin;
    }
}
