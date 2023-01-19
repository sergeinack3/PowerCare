<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Module;

use Exception;
use Ox\Core\Cache;
use Ox\Core\CApp;
use Ox\Core\Module\AbstractModuleCache;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CModuleAction;
use Ox\Mediboard\System\CPinnedTab;
use Ox\Mediboard\System\CTab;
use Ox\Tests\OxUnitTestCase;


class CModuleTest extends OxUnitTestCase
{

    public function testGetInstalledSuccess()
    {
        $module = CModule::getInstalled('system');
        $this->assertInstanceOf(CModule::class, $module);
    }

    public function testGetInstalledFailed()
    {
        $module = CModule::getInstalled('lorem');
        $this->assertNull($module);
    }

    public function testGetActiveSuccess()
    {
        $module = CModule::getActive('system');
        $this->assertInstanceOf(CModule::class, $module);
    }

    public function testGetActiveFailed()
    {
        $module = CModule::getActive('lorem');
        $this->assertNull($module);
    }

    public function testRegisterTabs()
    {
        $module = CModule::getActive('system');
        $this->assertEmpty($module->_tabs);
        Cache::deleteKeys(Cache::OUTER, 'CModule.registerTabs');
        $module->registerTabs();
        $this->assertNotEmpty($module->_tabs);

        return $module;
    }

    /**
     * @depends testRegisterTabs
     */
    public function testRegisterFromCache(CModule $module)
    {
        $module->_tabs = [];
        $module->registerTabs();
        $this->assertNotEmpty($module->_tabs);
    }

    public function testGetPinnedTabsEmpty(): void
    {
        CPinnedTab::removePinnedTabs('system');

        $module = CModule::getInstalled('system');
        $this->assertEmpty($module->getPinnedTabs());
        $this->assertArrayNotHasKey(CModule::TAB_PINNED, $module->_tabs);
    }

    public function testGetPinnedTabsOk(): void
    {
        CPinnedTab::removePinnedTabs('system');

        $module = CModule::getInstalled('system');
        $module->registerTabs();

        $pins = [
            $this->createPin($module, 'about'),
            $this->createPin($module, 'view_cache'),
        ];

        $bad_mod_pin = $this->createPin(CModule::getInstalled('dPpatients'), 'vw_correspondants');

        $not_available_pin = $this->createPin($module, uniqid());

        $mod_tabs = $module->getPinnedTabs();
        $this->assertCount(3, $mod_tabs);
        $this->assertArrayHasKey(CModule::TAB_PINNED, $module->_tabs);

        /** @var CPinnedTab $pin */
        foreach ($pins as $pin) {
            $this->assertArrayHasKey($pin->_tab_name, $module->_tabs[CModule::TAB_PINNED]);
        }

        $this->assertArrayNotHasKey($bad_mod_pin->_tab_name, $module->_tabs[CModule::TAB_PINNED]);
        $this->assertArrayNotHasKey($not_available_pin->_tab_name, $module->_tabs[CModule::TAB_PINNED]);
    }

    public function testBuildUrl(): void
    {
        $id = uniqid();

        $module           = new CModule();
        $module->mod_name = $id;

        $this->assertNull($module->_url);
        $module->buildUrl();

        $this->assertEquals('?m=' . $id, $module->_url);

        // URL already built, do not rebuild
        $module->mod_name = uniqid();
        $module->buildUrl();
        $this->assertEquals('?m=' . $id, $module->_url);
    }

    public function testGetTabs(): void
    {
        $module = CModule::getActive('dPplanningOp');

        $tabs = $module->getTabs();

        /** @var CTab $tab */
        foreach ($tabs as $tab) {
            $this->assertEquals($module->mod_name, $tab->mod_name);
            $this->assertStringContainsString('&tab=' . $tab->tab_name, $tab->getUrl());

            if ($tab->is_standard) {
                $this->assertArrayHasKey($tab->tab_name, $module->_tabs[CModule::TAB_STANDARD]);
                $this->assertFalse($tab->is_param);
                $this->assertFalse($tab->is_config);
                $this->assertNull($tab->pinned_order);
            }

            if ($tab->is_param) {
                $this->assertArrayHasKey($tab->tab_name, $module->_tabs[CModule::TAB_SETTINGS]);
                $this->assertFalse($tab->is_standard);
                $this->assertFalse($tab->is_config);
                $this->assertNull($tab->pinned_order);
            }

            if ($tab->is_config) {
                $this->assertArrayHasKey($tab->tab_name, $module->_tabs[CModule::TAB_CONFIGURE]);
                $this->assertFalse($tab->is_param);
                $this->assertFalse($tab->is_standard);
                $this->assertNull($tab->pinned_order);
            }

            if ($tab->pinned_order) {
                $this->assertArrayHasKey($tab->tab_name, $module->_tabs[CModule::TAB_PINNED]);
                $this->assertFalse($tab->is_param);
                $this->assertFalse($tab->is_config);
                $this->assertFalse($tab->is_standard);
            }
        }
    }

    public function testBuildTab(): void
    {
        $module = CModule::getActive('system');
        $module->registerTabs();

        $id  = uniqid();
        $tab = $module->buildTab($id);
        $this->assertEquals(
            new CTab('system', $id, false, false, false, null, '?m=system&tab=' . $id),
            $tab
        );

        $tab = $module->buildTab('about');
        $this->assertEquals(
            new CTab('system', 'about', true, false, false, null, $module->_tabs[CModule::TAB_STANDARD]['about']),
            $tab
        );
    }

    private function createPin(CModule $module, string $action): CPinnedTab
    {
        $mod_action = CModuleAction::getID($module->mod_name, $action);

        $pin                   = new CPinnedTab();
        $pin->module_id        = $module->_id;
        $pin->module_action_id = $mod_action;
        $pin->user_id          = CMediusers::get()->_id;
        $pin->loadMatchingObjectEsc();
        if ($msg = $pin->store()) {
            // If msg contains the mod_name and action the store failed because of duplicate unique key.
            // So the tab already exists.
            if (!str_contains($msg, $module->mod_name . ' - ' . $action)) {
                $this->fail($msg);
            }
        }

        return $pin;
    }

    /**
     * Check if all child of AbstractModuleCache overrides the abstract function getModuleName()
     *
     * @return void
     * @throws Exception
     */
    public function testModuleCacheName(): void
    {
        foreach (CApp::getChildClasses(AbstractModuleCache::class) as $class) {
            $this->assertTrue(
                CApp::isMethodOverridden($class, 'getModuleName'),
                "At least one of AbstractModuleCache child classes doesn't implement function getModuleName"
            );
        }
    }
}
