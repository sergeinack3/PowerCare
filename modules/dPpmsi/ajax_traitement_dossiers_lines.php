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
use Ox\Core\CView;
use Ox\Mediboard\Atih\CGroupage;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$period         = CView::get("period", "enum list|matin|soir");
$filterFunction = CView::get("filterFunction", "str");
$type           = CView::get("type", "enum list|ambucomp|ambucompssr|comp|ambu|exte|seances|ssr|psy|urg|consult default|ambu");
$service_id     = CView::get("service_id", "ref class|CService");
$prat_id        = CView::get("prat_id", "ref class|CMediusers");
$order_way      = CView::get("order_way", "enum list|ASC|DESC default|ASC");
$order_col      = CView::get("order_col", "str default|sortie_reelle");
$tri_recept     = CView::get("tri_recept", "str");
$tri_complet    = CView::get("tri_complet", "str");
$date           = CView::get("date", "date default|now", true);
CView::setSession("date", $date);
CView::checkin();

$service_id = explode(",", $service_id);
CMbArray::removeValue("", $service_id);

$group         = CGroups::loadCurrent();
$date_actuelle = CMbDT::dateTime("00:00:00");
$date_demain   = CMbDT::dateTime("00:00:00", "+ 1 day");
$hier          = CMbDT::date("- 1 day", $date);
$demain        = CMbDT::date("+ 1 day", $date);
$date_min      = CMbDT::dateTime("00:00:00", $date);
$date_max      = CMbDT::dateTime("23:59:59", $date);

// Entrées de la journée
$sejour = new CSejour();

// Lien avec les patients et les praticiens
$ljoin["patients"] = "sejour.patient_id = patients.patient_id";
$ljoin["users"]    = "sejour.praticien_id = users.user_id";

// Filtre sur les services
if (count($service_id)) {
  $ljoin["affectation"]       = "affectation.sejour_id = sejour.sejour_id AND affectation.sortie = sejour.sortie_reelle";
  $in_services                = CSQLDataSource::prepareIn($service_id);
  $where["sejour.service_id"] = " $in_services OR affectation.service_id $in_services";
}

// Filtre sur le type du séjour
if ($type == "ambucomp") {
  $where["sejour.type"] = " = 'ambu' OR `sejour`.`type` = 'comp'";
}
elseif ($type == "ambucompssr") {
  $where["sejour.type"] = " = 'ambu' OR `sejour`.`type` = 'comp' OR `sejour`.`type` = 'ssr'";
}
elseif ($type) {
  $where["sejour.type"] = " = '$type'";
}
else {
  $where["sejour.type"] = CSQLDataSource::prepareNotIn(CSejour::getTypesSejoursUrgence()) . " AND `sejour`.`type` != 'seances'";
}

// Filtre sur le praticien
if ($prat_id) {
  $where["sejour.praticien_id"] = " = '$prat_id'";
}

if ($period) {
  $hour = CAppUI::gconf("dPadmissions General hour_matin_soir");
  if ($period == "matin") {
    $date_max = CMbDT::dateTime($hour, $date);
  }
  else {
    $date_min = CMbDT::dateTime($hour, $date);
  }
}

if ($tri_recept) {
  $where["sejour.reception_sortie"] = " IS NULL";
}

if ($tri_complet) {
  $where["sejour.completion_sortie"] = " IS NULL";
}

$where["sejour.group_id"]      = "= '$group->_id'";
$where["sejour.sortie_reelle"] = "BETWEEN '$date_min' AND '$date_max'";
$where["sejour.annule"]        = "= '0'";

if ($order_col != "patient_id" && $order_col != "sortie_reelle" && $order_col != "praticien_id") {
  $order_col = "patient_id";
}

if ($order_col == "patient_id") {
  $order = "patients.nom $order_way, patients.prenom $order_way, sejour.entree_prevue";
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

$total          = 0;
$sejour_groupes = 0;
$groupages      = array();

// Chargement des NDA
CSejour::massLoadNDA($sejours);
foreach ($sejours as $sejour_id => $_sejour) {
  $_sejour->loadRefPatient();
  $praticien = $_sejour->loadRefPraticien();
  $rss       = $_sejour->loadRefRSS();

  $_sejour->loadRefTraitementDossier();
  if ($filterFunction && $filterFunction != $praticien->function_id) {
    unset($sejours[$sejour_id]);
    continue;
  }

  if ($rss->_id) {
    $groupage = new CGroupage();
    $groupage->launchFG($rss->_id);

    if ($groupage->_ref_infos_ghs) {
      $total += $groupage->_ref_infos_ghs->ghs_pri;
      if ($groupage->_ref_infos_ghs->ghm_nro !== "90Z00Z") {
        $sejour_groupes++;
      }
    }

    $groupages[$_sejour->_id] = $groupage;
  }
}

// Si la fonction selectionnée n'est pas dans la liste des fonction, on la rajoute
if ($filterFunction && !array_key_exists($filterFunction, $functions)) {
  $_function = new CFunctions();
  $_function->load($filterFunction);
  $functions[$filterFunction] = $_function;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejours", $sejours);
$smarty->assign("functions", $functions);
$smarty->assign("filterFunction", $filterFunction);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);
$smarty->assign('date', $date);
$smarty->assign("hier", $hier);
$smarty->assign("demain", $demain);
$smarty->assign("date_min", $date_min);
$smarty->assign("date_max", $date_max);
$smarty->assign("date_demain", $date_demain);
$smarty->assign("date_actuelle", $date_actuelle);
$smarty->assign("period", $period);
$smarty->assign("groupages", $groupages);
$smarty->assign("sejour_groupes", $sejour_groupes);
$smarty->assign("total", $total);
$smarty->display("traitement_dossiers/inc_traitement_dossiers_lines");