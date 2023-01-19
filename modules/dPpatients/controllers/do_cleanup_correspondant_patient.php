<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CValue;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Patients\CCorrespondantPatient;

/**
 * Script de nettoyage des correspondants patients: supprime les doublons de correspondants totalement identiques
 */
CCanDo::checkAdmin();

CApp::setTimeLimit(600);

$count_min   = CValue::post("count_min", 1);
$merge_dates = CValue::post("merge_dates", 0);
$dry_run     = CValue::post("dry_run");

HandlerManager::disableObjectHandlers();

CMbObject::$useObjectCache = false;

$correspondant = new CCorrespondantPatient();
$fields        = array_keys($correspondant->getPlainFields());

$spec = $correspondant->_spec;

// Don't group by key... of course
CMbArray::removeValue($spec->key, $fields);

// Don't group by date
if ($merge_dates) {
  CMbArray::removeValue("date_debut", $fields);
}

$select          = $fields;
$select["TOTAL"] = "COUNT(*)";
$select["IDS"]   = "GROUP_CONCAT(CAST({$spec->key} AS CHAR))";

$orderby = "TOTAL DESC";

$count_min = max(1, $count_min);
$having    = array(
  "TOTAL" => $spec->ds->prepare("> ?", $count_min)
);

$where = array(
  "patient_id" => "IS NOT NULL"
);

$request = new CRequest();
$request->addSelect($select);
$request->addTable($spec->table);
$request->addGroup($fields);
$request->addWhere($where);
$request->addOrder($orderby);
$request->addHaving($having);

$list = $spec->ds->loadList($request->makeSelect());

$count_total = 0;

foreach ($list as $_corresp) {
  $ids = explode(",", $_corresp["IDS"]);

  if (empty($ids)) {
    continue;
  }

  array_unique($ids);
  sort($ids);
  array_pop($ids); // Only keep last

  CAppUI::stepAjax(" -- Patient #" . $_corresp["patient_id"], UI_MSG_OK);

  $count = 0;
  foreach ($ids as $_id) {
    if ($dry_run) {
      $count++;
      continue;
    }

    $_correspondant = new CCorrespondantPatient();
    $_correspondant->load($_id);
    if ($msg = $_correspondant->delete()) {
      CAppUI::stepAjax($msg, UI_MSG_WARNING);
    }
    else {
      $count++;
    }
  }

  $count_total += $count;

  if ($dry_run) {
    CAppUI::stepAjax("$count correspondants à supprimer", UI_MSG_OK);
  }
  else {
    CAppUI::stepAjax("$count correspondants supprimés", UI_MSG_OK);
  }
}

if ($dry_run) {
  CAppUI::stepAjax("$count_total correspondants à supprimer au total", UI_MSG_OK);
}
else {
  CAppUI::stepAjax("$count_total correspondants supprimés au total", UI_MSG_OK);
}
