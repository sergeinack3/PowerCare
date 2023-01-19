<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Maternite\CGrossesse;

/**
 * Liste des grossesses dont le terme est proche
 */
$date           = CValue::getOrSession("date", CMbDT::date());
$show_cancelled = CValue::getOrSession("show_cancelled", 0);

$days_terme = CAppUI::gconf("maternite general days_terme");
$date_min   = CMbDT::date("- $days_terme days", $date);
$date_max   = CMbDT::date("+$days_terme days", $date);

$where                = array();
$ljoin                = array();
$where["terme_prevu"] = "BETWEEN '$date_min' AND '$date_max'";
$ljoin["patients"]    = "patients.patient_id = grossesse.parturiente_id";

$grossesse  = new CGrossesse();
$grossesses = $grossesse->loadGroupList($where, "terme_prevu DESC, nom ASC", null, null, $ljoin);

/** @var CStoredObject[] $grossesses */
$patientes = CStoredObject::massLoadFwdRef($grossesses, "parturiente_id");
CStoredObject::massLoadBackRefs($patientes, "bmr_bhre");
$sejours_grossesses       = CStoredObject::massLoadBackRefs($grossesses, "sejours", "entree_prevue DESC");
$consultations_grossesses = CStoredObject::massLoadBackRefs($grossesses, "consultations", "date DESC, heure DESC", null,
  array("plageconsult" => "plageconsult.plageconsult_id = consultation.plageconsult_id"));

CStoredObject::massLoadFwdRef($consultations_grossesses, "plageconsult_id");

/** @var CGrossesse[] $grossesses */
foreach ($grossesses as $_grossesse) {
  $sejours = $_grossesse->loadRefsSejours();
  if (!$show_cancelled && count($sejours) == 1 && reset($sejours)->annule == 1) {
    unset($grossesses[$_grossesse->_id]);
    continue;
  }
  $_grossesse->loadRefParturiente()->updateBMRBHReStatus();
  $_grossesse->loadLastConsultAnesth();
  $_grossesse->_ref_last_consult_anesth->loadRefPlageConsult();
}

$smarty = new CSmartyDP();

$smarty->assign("grossesses", $grossesses);
$smarty->assign("date", $date);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("show_cancelled", $show_cancelled);

$smarty->display("vw_grossesses.tpl");
