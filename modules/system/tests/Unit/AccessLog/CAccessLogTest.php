<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\System\Tests\Unit\AccessLog;

use DateTime;
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Mediboard\System\AccessLog\CAccessLog;
use Ox\Mediboard\System\CModuleAction;
use Ox\Tests\OxUnitTestCase;


class CAccessLogTest extends OxUnitTestCase
{

    public function testAggregateDryRun(): void
    {
        $access_log = new CAccessLog();
        $where      = [
            'period'    => "<= '" . CMbDT::date('- 1 MONTH') . "'",
            'aggregate' => "= '10'",
        ];

        $actual_count = $access_log->countList($where);

        $module_action_id             = CModuleAction::getID('system', 'about');
        $access_log->module_action_id = $module_action_id;

        $dt = new DateTime('now');
        $dt->modify('-1 month');
        $dt->modify('-1 day');

        $access_log->period    = $dt->format("Y-m-d H:i:s");
        $access_log->hits      = 1;
        $access_log->duration  = 0;
        $access_log->request   = 0;
        $access_log->aggregate = 10;

        $this->storeOrFailed($access_log);

        CAccessLog::aggregate(true, true, true);
        $msg = CAppUI::getMsg();
        $this->assertStringContainsString(($actual_count + 1) . ' logs to aggregate from level 10', $msg);
    }

    public function testAggregateLevelNext(): void
    {
        $access_log   = new CAccessLog();
        $where        = [
            'period'    => "<= '" . CMbDT::date('- 1 YEAR') . "'",
            'aggregate' => "= '60'",
        ];
        $actual_count = $access_log->countList($where);

        $module_action_id             = CModuleAction::getID('system', 'about');
        $access_log->module_action_id = $module_action_id;

        $dt = new DateTime('now');
        $dt->modify('-1 year');

        $access_log->period    = $dt->format("Y-m-d H:i:s");
        $access_log->hits      = 1;
        $access_log->duration  = 0;
        $access_log->request   = 0;
        $access_log->aggregate = 60;

        $this->storeOrFailed($access_log);

        CAccessLog::aggregate(false, true, true);
        $msg = CAppUI::getMsg();
        $this->assertStringContainsString(($actual_count + 1) . ' logs inserted to level 60', $msg);
    }

}
