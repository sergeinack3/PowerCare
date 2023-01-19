<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit\Sessions;

use Ox\Core\Sessions\CSessionManager;
use Ox\Tests\OxUnitTestCase;

/**
 * @runTestsInSeparateProcesses
 */
class CSessionManagerTest extends OxUnitTestCase
{
    /**
     * @config session_handler files
     */
    public function testSessionManagerGetFileSession(): void
    {
        $session_manager = CSessionManager::get();
        $this->assertEquals('files', $session_manager->getSessionHandler());
    }

    /**
     * @config session_handler mysql
     */
    public function testSessionManagerGetMySQLSession(): void
    {
        $session_manager = CSessionManager::get();
        $this->assertEquals('mysql', $session_manager->getSessionHandler());
    }

    /**
     * @config session_handler redis
     */
    public function testSessionManagerGetRedisSession(): void
    {
        $session_manager = CSessionManager::get();
        $this->assertEquals('redis', $session_manager->getSessionHandler());
    }
}
