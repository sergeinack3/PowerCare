<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Core\FileUtil\CCSVFile;
use Ox\Mediboard\System\AccessLog\CAccessLog;

CCanDo::checkRead();
$date     = CView::get("date", "date default|" . CMbDT::date(), true);
$groupres = CView::get("groupres", "num default|1", true);
$element  = CView::get("element", "str default|duration", true);
$interval = CView::get("interval", "str default|day", true);
$numelem  = CView::get("numelem", "num default|6", true);
$download = CView::get("download", "bool default|0");
CView::checkin();
CView::enableSlave();

CAppUI::requireModuleFile('dPstats', 'graph_ressourceslog');

$next     = CMbDT::date("+1 DAY", $date);
switch ($interval) {
  default:
  case "day":
    $from = CMbDT::date("-1 DAY", $next);
    break;
  case "month":
    $from = CMbDT::date("-1 MONTH", $next);
    break;
  case "year":
    $from = CMbDT::date("-6 MONTH", $next);
    break;
}

$graphs = array();
if ($groupres == 1) {
  if ($element != "_average_duration"
      && $element != "_average_request"
      && $element != "_average_php_duration"
      && $element != "_average_nb_requests"
  ) {
    $graphs[] = graphRessourceLog('modules', $date, $element, $interval, $numelem);
  }
  $graphs[] = graphRessourceLog('total', $date, $element, $interval, $numelem);
}
else {
  $logs = CAccessLog::loadAggregation($from, $next, ($groupres + 1), 0);
  foreach ($logs as $log) {
    $graphs[] = graphRessourceLog($log->_module, $date, $element, $interval, $numelem);
  }
}

if ($download) {
  $csv = new CCSVFile();
  foreach ($graphs as $_graph) {
    foreach ($_graph['series'] as $_serie) {
      $line = array(
        $_serie['module'],
        $_serie['label'],
        $_serie['data'][0][1],
      );
      $csv->writeLine($line);
    }
  }
  $csv->stream('palmares_ressources');
  CApp::rip();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("graphs"     , $graphs);
$smarty->assign("date"       , $date);
$smarty->assign("groupres"   , $groupres);
$smarty->assign("element"    , $element);
$smarty->assign("interval"   , $interval);
$smarty->assign("numelem"    , $numelem);
$smarty->assign("listModules", CModule::getInstalled());

$smarty->display('inc_vw_resource_logs.tpl');
