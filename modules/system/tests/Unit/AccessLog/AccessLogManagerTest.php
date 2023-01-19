<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\AccessLog;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\Config\Conf;
use Ox\Mediboard\System\AccessLog\AccessLogManager;
use Ox\Mediboard\System\CModuleAction;
use Ox\Mediboard\System\Controllers\SystemController;
use Ox\Tests\OxUnitTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;


class AccessLogManagerTest extends OxUnitTestCase
{

    public function testCreateFromGlobals(): void
    {
        global $m, $a, $action, $dosql;
        $m       = 'system';
        $a       = 'a';
        $action  = 'action';
        $manager = AccessLogManager::createFromGlobals();
        $this->assertEquals($manager->getModule(), 'system');
        $this->assertEquals($manager->getAction(), 'action');
    }

    public function testCreateFromRequest(): void
    {
        $request = new Request([], [], ['_controller' => SystemController::class . '::status']);

        $manager = AccessLogManager::createFromRequest($request);
        $this->assertEquals($manager->getModule(), 'system');
        $this->assertEquals($manager->getAction(), 'SystemController::status');
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSupports(): void
    {
        $request = new Request();
        $manager = AccessLogManager::createFromRequest($request);
        $this->assertFalse($manager->supports());

        $request        = new Request([], [], ['_controller' => SystemController::class . '::status']);
        $manager        = AccessLogManager::createFromRequest($request);

        CApp::$readonly = true;
        $this->assertFalse($manager->supports());

        CApp::$readonly = false;
        $this->assertTrue($manager->supports());

        $conf = $this->createMock(Conf::class);
        $conf->method("get")->with('log_access')->willReturn(false);
        $manager->setConf($conf);
        $this->assertFalse($manager->supports());
    }

    public function testLog(): void
    {
        $request = new Request([], [], ['_controller' => SystemController::class . '::status']);
        $manager = AccessLogManager::createFromRequest($request);

        // clear tmp buffer
        $buffer = CAppUI::getTmpPath("CAccessLog.buffer");
        $fs     = new Filesystem();
        $fs->remove($buffer);

        $module_action_id = CModuleAction::getID($manager->getModule(), $manager->getAction());

        // support+hydrate+bufferize
        $manager->log();
        $access_log = $manager->getAccessLog();

        $this->assertEquals($access_log->module_action_id, $module_action_id);

        if ($fs->exists($buffer)) {
            $logs              = file($buffer);
            $access_log_buffer = unserialize(end($logs));
            $this->assertEquals($access_log_buffer, $access_log);
        }
    }
}
