<?php

/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Tests\Unit;

use Ox\Core\CApp;
use Ox\Core\CMbObjectSpec;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Tests\OxUnitTestCase;

/**
 * Description
 */
class CMbObjectSpecTest extends OxUnitTestCase
{
    /**
     * @dataProvider loggableTrueProvider
     */
    public function testLoggableTrue(bool $is_robot, $loggable_type)
    {
        CApp::$is_robot            = $is_robot;
        $function                  = $this->getNewFunction();
        $function->_spec->loggable = $loggable_type;

        // No existing log
        $last_log = $function->loadLastLog();
        $this->assertNull($last_log->_id);

        if ($msg = $function->store()) {
            $this->fail($msg);
        }

        $last_log = $function->loadLastLog();
        $this->assertNotNull($last_log->_id);
    }

    public function loggableTrueProvider(): array
    {
        return [
            'Loggable always and user human'       => [false, CMbObjectSpec::LOGGABLE_ALWAYS],
            'Loggable always and user robot'       => [true, CMbObjectSpec::LOGGABLE_ALWAYS],
            'Loggable bot and user robot'          => [true, CMbObjectSpec::LOGGABLE_BOT],
            'Loggable human and user human'        => [false, CMbObjectSpec::LOGGABLE_HUMAN],
            'Loggable leggacy true and user human' => [false, CMbObjectSpec::LOGGABLE_LEGACY_TRUE],
            'Loggable leggacy true and user robot' => [true, CMbObjectSpec::LOGGABLE_LEGACY_TRUE],
        ];
    }

    /**
     * @dataProvider loggableFalseProvider
     */
    public function testLoggableFalse(bool $is_robot, $loggable_type)
    {
        CApp::$is_robot            = $is_robot;
        $function                  = $this->getNewFunction();
        $function->_spec->loggable = $loggable_type;

        // No existing log
        $last_log = $function->loadLastLog();
        $this->assertNull($last_log->_id);

        // Reset last user action var because of the CStoredObject->store
        $this->invokePrivateMethod($function, 'resetLastUserAction');

        if ($msg = $function->store()) {
            $this->fail($msg);
        }

        $last_log = $function->loadLastLog();
        $this->assertNull($last_log->_id);

        // Reset last user action var because of the CStoredObject->store
        $this->invokePrivateMethod($function, 'resetLastUserAction');

        // Delete function, user_log is disabled and function won't be deleted by FW
        $function->delete();
    }

    public function loggableFalseProvider(): array
    {
        return [
            'Loggable never and user human'         => [false, CMbObjectSpec::LOGGABLE_NEVER],
            'Loggable never and user robot'         => [true, CMbObjectSpec::LOGGABLE_NEVER],
            'Loggable leggacy false and user human' => [false, CMbObjectSpec::LOGGABLE_LEGACY_FALSE],
            'Loggable leggacy famse and user robot' => [true, CMbObjectSpec::LOGGABLE_LEGACY_FALSE],
            'Loggable human and user robot'         => [true, CMbObjectSpec::LOGGABLE_HUMAN],
            'Loggable bot and user human'           => [false, CMbObjectSpec::LOGGABLE_BOT],
        ];
    }

    private function getNewFunction(): CFunctions
    {
        $function           = new CFunctions();
        $function->group_id = CGroups::loadCurrent()->_id;
        $function->text     = uniqid();
        $function->color    = 'ffffff';
        $function->type     = 'administratif';

        return $function;
    }
}
