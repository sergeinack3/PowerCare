<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbRange;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CItemPrestation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CAppUI::requireModuleFile("dPhospi", "inc_vw_affectations");

$lit_id         = CView::get("lit_id", "ref class|CLit");
$mode_vue_tempo = CView::get("mode_vue_tempo", "enum list|classique|compact default|classique");
$date           = CView::get("date", "dateTime");
$granularite    = CView::get("granularite", "enum list|day|48hours|72hours|week|4weeks default|day");
$readonly       = CView::get("readonly", "bool default|0", true);
$prestation_id  = CView::get("prestation_id", "str"); // can be equal to 'all'
$nb_ticks       = CView::get("nb_ticks", "num");
$date_min       = CView::get("date_min", "dateTime");

CView::checkin();

$unite        = "";
$period       = "";
$datetimes    = array();
$change_month = array();
$group_id     = CGroups::loadCurrent()->_id;

switch ($granularite) {
  default:
  case "day":
    $unite       = "hour";
    $nb_unite    = 1;
    $step        = "+1 hour";
    $period      = "1hour";
    $date_before = CMbDT::date("-1 day", $date);
    $date_after  = CMbDT::date("+1 day", $date);
    break;
    case "48hours":
        $unite       = "hour";
        $nb_unite    = 2;
        $nb_ticks    = 24;
        $step        = "+2 hours";
        $period      = "2hours";
        $date_before = CMbDT::dateTime("-1 day", CMbDT::date($date));
        $date_after  = CMbDT::dateTime("+2 days", $date);
        break;
    case "72hours":
        $unite       = "hour";
        $nb_unite    = 3;
        $nb_ticks    = 24;
        $step        = "+3 hours";
        $period      = "3hours";
        $date_before = CMbDT::dateTime("-1 day", CMbDT::date($date));
        $date_after  = CMbDT::dateTime("+3 days", $date_min);
        break;
  case "week":
    $unite       = "hour";
    $nb_unite    = 6;
    $nb_ticks    = 28;
    $step        = "+6 hours";
    $period      = "6hours";
    $date_min    = CMbDT::dateTime("-2 days", CMbDT::date($date));
    $date_before = CMbDT::date("-1 week", $date);
    $date_after  = CMbDT::date("+1 day", $date);
    break;
  case "4weeks":
    $unite       = "day";
    $nb_unite    = 1;
    $nb_ticks    = 28;
    $step        = "+1 day";
    $period      = "1day";
    $date_min    = CMbDT::dateTime("-1 week", CMbDT::dirac("week", $date));
    $date_before = CMbDT::date("-4 week", $date);
    $date_after  = CMbDT::date("+4 week", $date);
}

$current = CMbDT::dirac("hour", CMbDT::dateTime());
$offset  = $nb_ticks * $nb_unite;

$date_max      = CMbDT::dateTime("+ $offset $unite", $date_min);
$temp_datetime = CMbDT::dateTime(null, $date_min);

for ($i = 0; $i < $nb_ticks; $i++) {
  $offset = $i * $nb_unite;

  $datetime    = CMbDT::dateTime("+ $offset $unite", $date_min);
  $datetimes[] = $datetime;
}

$lit = new CLit;
$lit->load($lit_id);
$lit->_ref_affectations        = array();
$chambre                       = $lit->loadRefChambre();
$chambre->_ref_lits[$lit->_id] = $lit;

$lits = array($lit_id => $lit);

$liaisons_items    = $lit->loadBackRefs("liaisons_items");
$items_prestations = CStoredObject::massLoadFwdRef($liaisons_items, "item_prestation_id");
$prestations_ids   = CMbArray::pluck($items_prestations, "object_id");

if (in_array($prestation_id, $prestations_ids)) {
  $inverse         = array_flip($prestations_ids);
  $item_prestation = $items_prestations[$inverse[$prestation_id]];
  if ($item_prestation->_id) {
    $lit->_selected_item = $item_prestation;
  }
  else {
    $lit->_selected_item = new CItemPrestation;
  }
}
else {
  $lit->_selected_item = new CItemPrestation;
}

// Chargement des affectations
$where           = array();
$where["lit_id"] = "= '$lit_id'";
$where["entree"] = "<= '$date_max'";
$where["sortie"] = ">= '$date_min'";

$affectation  = new CAffectation;
$affectations = $affectation->loadList($where, "parent_affectation_id ASC");

$affectations += loadAffectationsPermissions($chambre->loadRefService(), CMbDT::date($date), 1, $prestation_id);

CAffectation::massUpdateView($affectations);

// Ajout des prolongations anormales
// (séjours avec entrée réelle et sortie non confirmée et sortie < maintenant
$nb_days_prolongation = CAppUI::gconf("dPhospi vue_temporelle nb_days_prolongation");
if ($nb_days_prolongation) {
  $sejour = new CSejour();
  $max    = CMbDT::dateTime();
  $min    = CMbDT::date("-$nb_days_prolongation days", $max) . " 00:00:00";
  $where  = array(
    "sejour.entree_reelle" => "IS NOT NULL",
    "sejour.sortie_reelle" => "IS NULL",
    "sejour.sortie_prevue" => "BETWEEN '$min' AND '$max'",
    "sejour.confirme"      => "IS NULL",
    "sejour.group_id"      => "= '$group_id'",
    "sejour.annule"        => "= '0'"
  );

  if (!CAppUI::conf("dPhospi vue_temporelle prolongation_ambu", "CGroups-$group_id")) {
    $where["sejour.type"] = "!= 'ambu'";
  }

  /** @var CSejour[] $sejours_prolonges */
  $sejours_prolonges = $sejour->loadList($where);

  $affectations_prolong = array();
  foreach ($sejours_prolonges as $_sejour) {
    $aff = $_sejour->getCurrAffectation($_sejour->sortie);
    if (!$aff->_id || $aff->lit_id != $lit_id) {
      continue;
    }
    $aff->updateView();
    $aff->_is_prolong        = true;
    $affectations[$aff->_id] = $aff;
  }
}

$sejours    = CStoredObject::massLoadFwdRef($affectations, "sejour_id");
$patients   = CStoredObject::massLoadFwdRef($sejours, "patient_id");
$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadFwdRef($praticiens, "function_id");
CStoredObject::massLoadBackRefs($patients, "dossier_medical");
CPatient::massCountPhotoIdentite($patients);

foreach ($affectations as $_affectation_imc) {
  /* @var CAffectation $_affectation_imc */
  if (CAppUI::conf("dPhospi vue_temporelle show_imc_patient", "CService-" . $_affectation_imc->service_id)) {
    $_affectation_imc->loadRefSejour()->loadRefPatient()->loadRefLatestConstantes(null, array("poids", "taille"));
  }
}

$operations = array();

$suivi_affectation = false;

loadVueTempo($affectations, $suivi_affectation, $lits, $operations, $date_min, $date_max, $period, $prestation_id);

$intervals = array();
if (count($lit->_ref_affectations)) {
  foreach ($lit->_ref_affectations as $_affectation) {
    $intervals[$_affectation->_id] = array(
      "lower" => $_affectation->entree,
      "upper" => $_affectation->sortie,
    );
  }
  $lit->_lines = CMbRange::rearrange($intervals);
}

// Pour les alertes, il est nécessaire de charger les autres lits
// de la chambre concernée ainsi que les affectations

$where           = array();
$where["entree"] = "<= '$date_max'";
$where["sortie"] = ">= '$date_min'";

$lits_ids = $chambre->loadBackIds("lits");

foreach ($lits_ids as $_lit_id) {
  if ($lit_id == $_lit_id) {
    continue;
  }
  $_lit = new CLit();
  $_lit->load($_lit_id);

  $where["lit_id"] = "= '$_lit->_id'";

  $_affectations = $affectation->loadList($where);
  CAffectation::massUpdateView($_affectations);

  $_sejours = CStoredObject::massLoadFwdRef($_affectations, "sejour_id");
  CStoredObject::massLoadFwdRef($_sejours, "patient_id");
  CStoredObject::massLoadFwdRef($_sejours, "praticien_id");

  /** @var $_affectations CAffectation[] */
  foreach ($_affectations as $_affectation) {
    $_sejour = $_affectation->loadRefSejour();
    $_sejour->loadRefPraticien();
    $_sejour->loadRefPatient()->loadRefsPatientHandicaps();
  }

  $_lit->_ref_affectations = $_affectations;

  $chambre->_ref_lits[$_lit->_id] = $_lit;
}

if (!CAppUI::gconf("dPhospi vue_temporelle hide_alertes_temporel")) {
  $lit->_ref_chambre->checkChambre();
}

$smarty = new CSmartyDP;

$smarty->assign("affectations", $affectations);
$smarty->assign("readonly", $readonly);
$smarty->assign("_lit", $lit);
$smarty->assign("date", $date);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("granularite", $granularite);

if ($prestation_id) {
  $smarty->assign("nb_ticks", $prestation_id ? $nb_ticks + 2 : $nb_ticks + 1);
}

$smarty->assign("nb_ticks_r", $nb_ticks - 1);
$smarty->assign("datetimes", $datetimes);
$smarty->assign("current", $current);
$smarty->assign("mode_vue_tempo", $mode_vue_tempo);
$smarty->assign("prestation_id", $prestation_id);
$smarty->assign("suivi_affectation", $suivi_affectation);
$smarty->assign("td_width", CAffectation::$width_vue_tempo / $nb_ticks);

$smarty->display("inc_line_lit.tpl");
