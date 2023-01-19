<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit;

use Ox\Core\Module\CModule;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Admin\Tests\Fixtures\UsersFixtures;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CModuleAction;
use Ox\Mediboard\System\CTabHit;
use Ox\Tests\OxUnitTestCase;

/**
 * Tests for CTabHit class
 */
class CTabHitTest extends OxUnitTestCase
{
    private $current_user;
    private $other_user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->current_user = CMediusers::get();
    }

    public function testRemoveOldHitsEmpty(): void
    {
        $this->other_user = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_LOREM_IPSUM);

        $this->deleteHits($this->current_user);
        $this->deleteHits($this->other_user);

        $this->createHits($this->current_user, 50);
        $this->createHits($this->other_user, 150);

        $this->assertEquals(50, $this->current_user->countBackRefs('visited_tabs', [], [], false));
        $this->assertEquals(150, $this->other_user->countBackRefs('visited_tabs', [], [], false));

        $hit = new CTabHit();
        $hit->user_id = $this->current_user;
        $hit->module_action_id = CModuleAction::getID('system', 'about');

        $hit->removeOldHits();

        $this->assertEquals(50, $this->current_user->countBackRefs('visited_tabs', [], [], false));
        $this->assertEquals(150, $this->other_user->countBackRefs('visited_tabs', [], [], false));
    }

    public function testRemoveOldHits(): void
    {
        $this->other_user = $this->getObjectFromFixturesReference(CMediusers::class, UsersFixtures::REF_USER_LOREM_IPSUM);

        $this->deleteHits($this->current_user);
        $this->deleteHits($this->other_user);

        $this->createHits($this->current_user, 150);
        $this->createHits($this->other_user, 150);

        $this->assertEquals(150, $this->current_user->countBackRefs('visited_tabs', [], [], false));
        $this->assertEquals(150, $this->other_user->countBackRefs('visited_tabs', [], [], false));

        $hit = new CTabHit();
        $hit->user_id = $this->current_user->_id;
        $hit->module_action_id = CModuleAction::getID('system', 'about');

        $hit->removeOldHits();

        $this->assertEquals(100, $this->current_user->countBackRefs('visited_tabs', [], [], false));
        $this->assertEquals(150, $this->other_user->countBackRefs('visited_tabs', [], [], false));
    }

    public function testRegisterHitNonExistingTab(): void
    {
        $module = CModule::getActive('system');
        $module->registerTabs();

        $this->assertNull(CTabHit::registerHit($module, uniqid()));
    }

    public function testRegisterHitOk(): void
    {
        $module = CModule::getActive('system');
        $module->registerTabs();

        $tab_hit = CTabHit::registerHit($module, 'about');
        $this->assertNotNull($tab_hit);
        $this->assertNotNull($tab_hit->_id);
    }

    public function testGetMostCalledTabsEmpty(): void
    {
        $this->deleteHits($this->current_user);
        $this->assertEmpty((new CTabHit())->getMostCalledTabs($this->current_user, 100));
    }

    public function testGetMostCalledTabsOk(): void
    {
        $this->deleteHits($this->current_user);

        $this->createHits($this->current_user, 2, 'about');
        $this->createHits($this->current_user, 4, 'view_modules');
        $this->createHits($this->current_user, 3, 'view_cache');
        $this->createHits($this->current_user, 5, 'configure');

        $tabs = (new CTabHit())->getMostCalledTabs($this->current_user, 10);
        $this->assertCount(4, $tabs);

        $config = array_shift($tabs);
        $this->assertEquals('system', $config->mod_name);
        $this->assertEquals('configure', $config->tab_name);

        $view_modules = array_shift($tabs);
        $this->assertEquals('system', $view_modules->mod_name);
        $this->assertEquals('view_modules', $view_modules->tab_name);

        $view_cache = array_shift($tabs);
        $this->assertEquals('system', $view_cache->mod_name);
        $this->assertEquals('view_cache', $view_cache->tab_name);

        $about = array_shift($tabs);
        $this->assertEquals('system', $about->mod_name);
        $this->assertEquals('about', $about->tab_name);
    }

    private function deleteHits(CMediusers $user): void
    {
        $hit = new CTabHit();
        $ds = $hit->getDS();
        $hit->deleteAll($hit->loadIds(['user_id' => $ds->prepare('= ?', $user->_id)]));
    }

    private function createHits(CMediusers $user, int $count, string $tab = 'about'): array
    {
        $hits = [];
        for ($i = 0; $i < $count; $i++) {
            $hit = new CTabHit();
            $hit->user_id = $user->_id;
            $hit->module_action_id = CModuleAction::getID('system', $tab);
            if ($msg = $hit->store()) {
                $this->fail($msg);
            }

            $hits[$hit->_id] = $hit;
        }

        return $hits;
    }
}
