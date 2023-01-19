<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Core\Logger\LoggerLevels;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CApp::setTimeLimit(300);
CApp::setMemoryLimit('3072M');

CCanDo::checkAdmin();
$duration      = CView::get('duration', 'num default|1');
$sejour_type   = CView::get('sejour_type', 'str');
$service_id    = CView::get('service_id', 'num');
$limitation    = CView::get('limitation', 'bool default|1');
$all_no_finish = CView::get('all_no_finish', 'bool default|0');
CView::checkin();

$types_sejour = array();
if ($sejour_type) {
  $types_sejour = array($sejour_type);
}
if ($all_no_finish) {
  $types_sejour =  array("comp", "ssr", "psy");
}

$date = CMbDT::date();
$time = CMbDT::dateTime();
$group = CGroups::loadCurrent();

/* Check if the execution time is between 20h and 00h */
if ($limitation && !($time >= "$date 20:00:00" && $time <= "$date 23:59:59")) {
  CApp::log('Tâche exécutée hors de la plage de temps autorisée (20h-00h)', null, LoggerLevels::LEVEL_WARNING);
}
else {
  $where = array(
    'group_id'      => " = $group->_id",
    'sortie_reelle' => ' IS NULL',
    'annule'        => " = '0'"
  );

  $where[] = "DATE(sortie_prevue) ".($all_no_finish?">":"")."= '$date'";

  /* Filter on the type of sejour */
  if (count($types_sejour)) {
    $where['type'] = CSQLDataSource::prepareIn($types_sejour);
  }

  /* Filter on the service */
  if ($service_id) {
    $where['service_id'] = " = $service_id";
  }

  $sejour  = new CSejour();
  $sejours = $sejour->loadList($where, null, null, 'sejour_id');

  foreach ($sejours as $_sejour) {
    $_sejour->sortie_prevue = CMbDT::dateTime("+ $duration days", $_sejour->sortie_prevue);

    /* Change the type for the type 'ambu' */
    if ($_sejour->type == 'ambu') {
      $_sejour->type = 'comp';
    }

    $_sejour->updateFormFields();

    $msg = $_sejour->store();
    if ($msg) {
      CApp::log("Séjour en erreur : '$_sejour->_guid', $msg", null, LoggerLevels::LEVEL_WARNING);
    }
  }
}
