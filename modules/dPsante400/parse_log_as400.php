<?php
/**
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkAdmin();

$max_rows     = CView::request("max_rows", "num default|1000");
$max_lines    = CView::request("max_lines", "num default|1000000");
$phase        = CView::request("phase", "enum list|connection|query|multiple_load_execute");
$min_duration = CView::request("min_duration", "num default|1");
CView::checkin();

$phase = strtr($phase, "_", " ");

$path = CAppUI::getTmpPath("mb-log.html");
if (!$resource = fopen($path, "r")) {
  CAppUI::stepMessage(UI_MSG_ERROR, "no log available");
}

$lines_count = 0;
$entries     = array();
while (($line = fgets($resource)) !== false) {
  if ($max_lines && $lines_count >= $max_lines) {
    break;
  }
  $lines_count++;

  $matches = array();
  if (preg_match("/\[(.*)\] CRecordSante400: slow '(.*)' in '(.*)' seconds/", $line, $matches)) {
    $entry = array(
      "datetime" => $matches[1],
      "phase"    => $matches[2],
      "duration" => $matches[3],
    );

    // Filter on actions
    if ($phase && $entry["phase"] != $phase) {
      continue;
    }

    // Filter on duration
    if ($min_duration && $entry["duration"] < $min_duration) {
      continue;
    }

    $entries[] = $entry;
  }
}

$entries    = array_reverse($entries);
$table      = array();
$rows_count = 0;
foreach ($entries as $_entry) {
  if ($max_rows && $rows_count >= $max_rows) {
    break;
  }
  $rows_count++;

  $date                  = CMbDT::date($_entry["datetime"]);
  $hour                  = CMbDT::format($_entry["datetime"], "%Y-%m-%d %H:00:00");
  $table[$date][$hour][] = $_entry;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("params", CView::$params);
$smarty->assign("entries_count", count($entries));
$smarty->assign("rows_count", $rows_count);
$smarty->assign("lines_count", $lines_count);
$smarty->assign("table", $table);
$smarty->display("parse_log_as400.tpl");
