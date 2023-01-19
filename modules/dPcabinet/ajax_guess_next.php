<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$type         = CValue::get("type");
$nb           = CValue::get("number", 1);
$dates        = CValue::get("dates");
$chir_id      = CValue::get("chir_id");
$function_id  = CView::getRefCheckRead("function_id", 'ref class|CFunctions');

CView::checkin();

$plages = array();

if (!$type || !$nb) {
  CApp::json($plages);
}

// get current
$plage_consult = new CPlageconsult();
$ds = $plage_consult->getDS();
$where = [];
$where['date'] = CSQLDataSource::prepareIn($dates);
$where['locked'] = " != '1' ";

if ($chir_id) {
  $where['chir_id'] = " = '$chir_id' ";
  $plage_consult->loadObject($where, "date asc");
}
if (!$plage_consult->_id && $function_id) {
  $mediuser = new CMediusers();
  $users = $mediuser->loadProfessionnelDeSante(PERM_READ, $function_id);
  $where["chir_id"] = $ds->prepareIn(array_keys($users));
  $plage_consult->loadObject($where);
}

if (!$plage_consult->_id) {
  CApp::json($plages);
}

// guess next dates
$guess_dates = [];
for ($a = 1; $a <= $nb; $a++) {
  foreach ($dates as $_date) {
    $guess_dates[] = CMbDT::date("+$a $type", $_date);
  }
}

$where["date"] = CSQLDataSource::prepareIn($guess_dates);
$plages = $plage_consult->loadList($where, "date ASC");

$date_plage = array();
// fill out
foreach ($guess_dates as $nb => $_date) {
  $date_plage[$_date] = array();
}
foreach ($plages as $_plage) {
  $date_plage[$_plage->date] = $_plage->_id;
  //$date_plage[$_plage->date][] = $_plage->_id;  //@TODO : array
}

$results = array();
foreach ($guess_dates as $nb => $_date) {
  // try to find something else if no result on date
  if (!count($date_plage[$_date])) {
    $results[] = $_date;
    continue;
  }
  $results[] = $date_plage[$_date];
}

CApp::json($results);
