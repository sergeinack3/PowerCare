<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbException;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\System\CUserLog;

CCanDo::checkRead();
CView::checkin();

// Migration user_log > user_action
if (CAppUI::conf("activer_migration_log_to_action")) {
    throw new CMbException("Le cron de migration ne doit pas être executé si la migration probabiliste est activée.");

}

$limit = CAppUI::conf("migration_log_to_action_nbr");

$log = new CUserLog();
$log->migrationLogToAction($limit, null, true);

echo CAppUI::getMsg();


