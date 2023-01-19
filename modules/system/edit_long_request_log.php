<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Redis\CRedisClient;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\System\CLongRequestLog;

CCanDo::checkAdmin();

$log_id = CView::get("log_id", "ref class|CLongRequestLog");

CView::checkin();

$log = new CLongRequestLog();
$log->load($log_id);
$log->getLink();
$log->getModuleAction();
$log->loadRefSession();

if (!$log->isPublic()) {
    $log->loadRefUser()->loadRefFunction();
}

$smarty = new CSmartyDP();
$smarty->assign("log", $log);

$smarty->display("edit_long_request_log.tpl");

/**
 * Very ugly HTML injection hack waiting for better report display from a template
 */
echo "<div id='query-report' style='display: none;'>";
CSQLDataSource::displayReport($log->_query_report['sql']);
CRedisClient::displayReport($log->_query_report['nosql']);
echo "</div>";
