<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$group = CGroups::loadCurrent();

$filterFunction = CValue::getOrSession("filterFunction");
$date           = CValue::getOrSession("date", CMbDT::date());
$date_end       = CValue::get('date_end', $date);
$type           = CValue::getOrSession("type");
$service_id     = CValue::getOrSession("service_id");
$service_id     = explode(",", $service_id);
CMbArray::removeValue("", $service_id);
$prat_id        = CValue::getOrSession("prat_id");
$order_way      = CValue::getOrSession("order_way", "ASC");
$order_col      = CValue::getOrSession("order_col", "sortie_reelle");
$tri_recept     = CValue::getOrSession("tri_recept");
$tri_complet    = CValue::getOrSession("tri_complet");
$date           = CValue::getOrSession("date", CMbDT::date());
$period         = CValue::getOrSession("period");
$facturable     = CValue::getOrSession("facturable");
$sans_dmh       = CValue::getOrSession("sans_dmh");

$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");
$hier     = CMbDT::date("- 1 day", $date);
$demain   = CMbDT::date("+ 1 day", $date);
$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:59", $date_end);

// Entrées de la journée
$sejour = new CSejour();

// Lien avec les patients et les praticiens
$ljoin["patients"] = "sejour.patient_id = patients.patient_id";
$ljoin["users"] = "sejour.praticien_id = users.user_id";

// Filtre sur les services
if (count($service_id)) {
  $ljoin["affectation"] = "affectation.sejour_id = sejour.sejour_id AND affectation.sortie = sejour.sortie_reelle";
  $in_services = CSQLDataSource::prepareIn($service_id);
  $where[] = "(sejour.service_id $in_services OR affectation.service_id $in_services)";
}

// Filtre sur le type du séjour
if ($type == "ambucomp") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp'";
}
elseif ($type == "ambucompssr") {
  $where[] = "`sejour`.`type` = 'ambu' OR `sejour`.`type` = 'comp' OR `sejour`.`type` = 'ssr'";
}
elseif ($type) {
  $where["sejour.type"] = " = '$type'";
}

// Filtre sur le praticien
if ($prat_id) {
  $where["sejour.praticien_id"] = " = '$prat_id'";
}

if ($facturable != "") {
  $where["sejour.facturable"] = "= '$facturable'";
}

if ($sans_dmh) {
  $where["sejour.sans_dmh"] = "= '1'";
}

if ($period) {
  $hour = CAppUI::gconf("dPadmissions General hour_matin_soir");
  if ($period == "matin") {
    $date_max = CMbDT::dateTime($hour, $date_end);
  }
  else {
    $date_min = CMbDT::dateTime($hour, $date);
  }
}

if ($tri_recept == 1) {
  $where["sejour.reception_sortie"] = " IS NULL";
}
else if ($tri_recept == 2) {
  $where["sejour.reception_sortie"] = " IS NOT NULL";
}

if ($tri_complet == 1) {
  $where["sejour.completion_sortie"] = " IS NULL";
}
else if ($tri_complet == 2) {
  $where["sejour.completion_sortie"] = " IS NOT NULL";
}

$where["sejour.group_id"] = "= '$group->_id'";
$where["sejour.sortie_reelle"]   = "BETWEEN '$date_min' AND '$date_max'";
$where["sejour.annule"]   = "= '0'";

if (!in_array($order_col, array("patient_id", "entree_reelle", "sortie_reelle", "praticien_id"))) {
  $order_col = "patient_id";
}

if ($order_col == "patient_id") {
  $order = "patients.nom $order_way, patients.prenom $order_way, sejour.entree_prevue";
}

if ($order_col == "entree_reelle") {
  $order = "sejour.entree_reelle $order_way, patients.nom, patients.prenom";
}

if ($order_col == "sortie_reelle") {
  $order = "sejour.sortie_reelle $order_way, patients.nom, patients.prenom";
}

if ($order_col == "praticien_id") {
  $order = "users.user_last_name $order_way, users.user_first_name";
}

/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, $order, null, "sejour_id", $ljoin);

// Mass preloading
$patients   = CStoredObject::massLoadFwdRef($sejours, "patient_id");
$praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
$functions  = CStoredObject::massLoadFwdRef($praticiens, "function_id");
$operations = CStoredObject::massLoadBackRefs($sejours, "operations", "date ASC", array("operations.annulee" => "= '0'"));
CStoredObject::massLoadBackRefs($sejours, "relance");

// Chargement des NDA
CSejour::massLoadNDA($sejours);

foreach ($sejours as $sejour_id => $_sejour) {
  $praticien = $_sejour->loadRefPraticien();
  if ($filterFunction && $filterFunction != $praticien->function_id) {
    unset($sejours[$sejour_id]);
    continue;
  }

  $_sejour->loadRefPatient();
  $_sejour->loadRefsOperations();
  $_sejour->loadRefRelance();
}

// Si la fonction selectionnée n'est pas dans la liste des fonction, on la rajoute
if ($filterFunction && !array_key_exists($filterFunction, $functions)) {
  $_function = new CFunctions();
  $_function->load($filterFunction);
  $functions[$filterFunction] = $_function;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("sejours"       , $sejours);
$smarty->assign("functions"     , $functions);
$smarty->assign("filterFunction", $filterFunction);
$smarty->assign("order_way"     , $order_way);
$smarty->assign("order_col"     , $order_col);
$smarty->assign('date'          , $date);
$smarty->assign('date_end'      , $date_end);
$smarty->assign("hier"          , $hier);
$smarty->assign("demain"        , $demain);
$smarty->assign("date_min"      , $date_min);
$smarty->assign("date_max"      , $date_max);
$smarty->assign("date_demain"   , $date_demain);
$smarty->assign("date_actuelle" , $date_actuelle);
$smarty->assign("period"        , $period);

$smarty->display("reception_dossiers/inc_recept_dossiers_lines");
