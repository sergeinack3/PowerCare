<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CTransmissionMedicale;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\Prescription\CCategoryPrescription;

CCanDo::checkRead();

$transmission_id = CValue::get("transmission_id");
$from_compact    = CValue::get("from_compact", 0);

$transmission = new CTransmissionMedicale();
$transmission->load($transmission_id);
$transmission->loadTargetObject();

$macrocible = $transmission->_ref_object instanceof CCategoryPrescription && $transmission->_ref_object->cible_importante;

if ($transmission->_ref_object instanceof CAdministration) {
  $transmission->_ref_object->loadRefsFwd();
}

$trans            = new CTransmissionMedicale();
$trans->sejour_id = $transmission->sejour_id;

$transmission->loadRefCible();

if ($transmission->_ref_cible->report) {
  $trans->cible_id = $transmission->cible_id;
}
else {
  $trans->object_id    = $transmission->object_id;
  $trans->object_class = $transmission->object_class;
  $trans->libelle_ATC  = $transmission->libelle_ATC;
}

/** @var CTransmissionMedicale[] $trans */
$trans = $trans->loadMatchingList("date DESC, transmission_medicale_id ASC");
CStoredObject::massLoadFwdRef($trans, "sejour_id");
CStoredObject::massLoadFwdRef($trans, "user_id");

$transmissions = array();

foreach ($trans as $_trans) {
  $_trans->canDo();
  $_trans->loadRefSejour();
  $_trans->loadRefUser()->loadRefFunction();
  $_trans->loadTargetObject();

  if ($_trans->_ref_object instanceof CAdministration) {
    $_trans->_ref_object->loadRefsFwd();
  }

  $sort_key_pattern = "$_trans->date $_trans->_class $_trans->user_id $_trans->object_id $_trans->object_class $_trans->libelle_ATC";

  $sort_key = "$_trans->date $sort_key_pattern";

  $date_before     = CMbDT::dateTime("-1 SECOND", $_trans->date);
  $sort_key_before = "$date_before $sort_key_pattern";

  $date_after     = CMbDT::dateTime("+1 SECOND", $_trans->date);
  $sort_key_after = "$date_after $sort_key_pattern";

  // Aggrégation à -1 sec
  if (array_key_exists($sort_key_before, $transmissions)) {
    $sort_key = $sort_key_before;
  }
  // à +1 sec
  else {
    if (array_key_exists($sort_key_after, $transmissions)) {
      $sort_key = $sort_key_after;
    }
  }

  if (!isset($transmissions[$sort_key])) {
    $transmissions[$sort_key] = array("data" => array(), "action" => array(), "result" => array());
  }
  if (!isset($transmissions[$sort_key][0])) {
    $transmissions[$sort_key][0] = $_trans;
  }
  $transmissions[$sort_key][$_trans->type][] = $_trans;
}

$smarty = new CSmartyDP();

$smarty->assign("transmission", $transmission);
$smarty->assign("transmissions", $transmissions);
$smarty->assign("from_compact", $from_compact);
$smarty->assign("macrocible", $macrocible);

$smarty->display("inc_list_locked_trans.tpl");