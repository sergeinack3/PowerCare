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
use Ox\Core\CMbObject;
use Ox\Core\CRequest;
use Ox\Core\CValue;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Patients\CCorrespondant;

/**
 * Script de nettoyage des correspondants patients: supprime les doublons de correspondants totalement identiques
 */
CCanDo::checkAdmin();

CApp::setTimeLimit(600);

$count_min = CValue::post("count_min", 1);
$dry_run   = CValue::post("dry_run");

HandlerManager::disableObjectHandlers();

CMbObject::$useObjectCache = false;

$correspondant = new CCorrespondant();
$fields        = array("patient_id", "medecin_id");

$spec = $correspondant->_spec;

$select          = $fields;
$select["TOTAL"] = "COUNT(*)";
$select["IDS"]   = "GROUP_CONCAT(CAST({$spec->key} AS CHAR))";

$orderby = "TOTAL DESC";

$count_min = max(1, $count_min);
$having    = array(
  "TOTAL" => $spec->ds->prepare("> ?", $count_min)
);

$where = array();

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

    $_correspondant = new CCorrespondant();
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
    CAppUI::stepAjax("$count correspondants � supprimer", UI_MSG_OK);
  }
  else {
    CAppUI::stepAjax("$count correspondants supprim�s", UI_MSG_OK);
  }
}

if ($dry_run) {
  CAppUI::stepAjax("$count_total correspondants � supprimer au total", UI_MSG_OK);
}
else {
  CAppUI::stepAjax("$count_total correspondants supprim�s au total", UI_MSG_OK);
}
