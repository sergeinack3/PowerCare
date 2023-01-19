<?php

/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\Controllers;

use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Cache;
use Ox\Core\Module\CModule;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\Controllers\PreferencesController;
use Ox\Mediboard\System\CPreferences;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Description
 */
class PreferencesControllerTest extends OxUnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Cache::deleteKeys(Cache::OUTER, PreferencesController::CACHE_PREFIX . '-');
    }

    public function testModuleDoesNotExists(): void
    {
        $controller = new PreferencesController();
        $this->expectExceptionMessage("Module 'dPtoto' does not exists or is not active");
        $this->invokePrivateMethod($controller, 'loadModulePrefs', 'toto');
    }

    public function testLoadAllModulesPrefs(): void
    {
        CPreferences::$modules = [];

        $root_dir = dirname(__DIR__, 4);
        foreach (CModule::getActive() as $_mod) {
            $pref_file = $root_dir . '/' . $_mod->mod_name . '/preferences.php';
            if (file_exists($pref_file)) {
                include $pref_file;
            }
        }

        $expected_prefs = [];
        foreach (CPreferences::$modules as $_prefs) {
            $expected_prefs = array_merge($expected_prefs, $_prefs);
        }

        $controller = new PreferencesController();
        $prefs      = $this->invokePrivateMethod($controller, 'loadAllModulesPrefs');

        sort($expected_prefs);
        sort($prefs);
        $this->assertEquals($expected_prefs, $prefs);
    }

    public function testLoadModulePrefs(): void
    {
        CPreferences::$modules['system'] = [];
        include dirname(__DIR__, 3) . '/preferences.php';
        $expected_prefs = CPreferences::$modules['system'];

        $controller = new PreferencesController();
        $prefs      = $this->invokePrivateMethod($controller, 'loadModulePrefs', 'system');
        $this->assertEquals($expected_prefs, $prefs);
    }

    public function testLoadModuleWithNoPrefs(): void
    {
        $controller = new PreferencesController();
        $prefs      = $this->invokePrivateMethod($controller, 'loadModulePrefs', 'openData');
        $this->assertEquals([], $prefs);
    }

    public function testDefaultPreferencesResponseIsOk(): void
    {
        $request_api = RequestApi::createFromRequest(new Request());
        $controller  = new PreferencesController();
        $response    = $controller->listPreferences('system', $request_api);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('preferences', $content['data']['type']);

        $this->assertTrue(count($content['data']['attributes']) > 0);
    }

    public function testDefaultPreferencesResponseIsOkWithDp(): void
    {
        $request_api = RequestApi::createFromRequest(new Request());
        $controller  = new PreferencesController();
        $response    = $controller->listPreferences('patients', $request_api);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('preferences', $content['data']['type']);

        $this->assertTrue(count($content['data']['attributes']) > 0);
    }

    public function testListUsersPreferences(): void
    {
        $request_api = RequestApi::createFromRequest(new Request());
        $controller  = new PreferencesController();
        $response    = $controller->listUserPreferences('system', CMediusers::get()->loadRefUser(), $request_api);

        $this->assertEquals(200, $response->getStatusCode());

        $content = json_decode($response->getContent(), true);
        $this->assertEquals('preferences', $content['data']['type']);

        $this->assertTrue(count($content['data']['attributes']) > 0);
    }
}
