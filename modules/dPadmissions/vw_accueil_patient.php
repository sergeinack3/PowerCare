<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$filter = new CSejour();
$filter->_date_min_stat = CView::get("_date_min_stat", "date default|now");
$filter->_date_max_stat = CView::get("_date_max_stat", "date default|now");
$filter->type           = CView::get("type", "str", true);
$filter->_statut_pec    = CView::get("_statut_pec", "str", true);
$filter->praticien_id   = CView::get("praticien_id", "num", true);
$type_pec               = CView::get("type_pec", "str default|".$filter->_specs["type_pec"]->list);
$enabled_service        = CView::get("active_filter_services", "bool default|0", true);
$period                 = CView::get("period", "str", true);
$order_col              = CView::get("order_col", "str default|patient_id", true);
$order_way              = CView::get("order_way", "str default|ASC", true);
$only_list              = CView::get("only_list", "str");
$sejour_guid            = CView::get("sejour_guid", "str");
$sejours_ids            = CView::get("sejours_ids", "str", true);
$services_ids           = CView::get("services_ids", "str", true);
$date_interv_eg_entree  = CView::get("date_interv_eg_entree", "bool default|0", true);

$services_ids = CService::getServicesIdsPref($services_ids);
$sejours_ids  = CSejour::getTypeSejourIdsPref($sejours_ids);
CView::checkin();

$type_pref = array();

// Liste des types d'admission possibles
$list_type_admission = $filter->_specs["_type_admission"]->_list;

if (is_array($sejours_ids)) {
  CMbArray::removeValue("", $sejours_ids);

  // recupere les préférences des differents types de séjours selectionnés par l'utilisateur
  foreach ($sejours_ids as $key) {
    if ($key != 0) {
      $type_pref[] = $list_type_admission[$key];
    }
  }
}

// Récupération de la liste des praticiens
$prat  = CMediusers::get();
$prats = $prat->loadPraticiens();

$sejours = array();

if ($only_list) {
  //Recherche des séjours
  $ljoin = array();
  $where = array();
  $where["sejour.group_id"] = " = '".CGroups::loadCurrent()->_id."'";
  $where["annule"]   = " = '0'";

  // filtre sur les services
  if (count($services_ids) && $enabled_service) {
    $ljoin["affectation"]         = " affectation.sejour_id = sejour.sejour_id AND affectation.entree = sejour.entree";
    $where_services = "affectation.service_id ". CSQLDataSource::prepareIn($services_ids);
    if (in_array("NP", $services_ids)) {
      $where_services .= " OR affectation.affectation_id IS NULL";
    }
    $where[] = $where_services;
  }

  $where["sejour.entree_prevue"] = " BETWEEN '$filter->_date_min_stat 00:00:00' AND '$filter->_date_max_stat 23:59:59'";
  if (count($type_pref)) {
    $where["sejour.type"] = CSQLDataSource::prepareIn($type_pref);
  }
  else {
    $where["sejour.type"] = CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ["seances"]));
  }
  if ($type_pec) {
    // filtre sur les types pec des sejours
    $where["sejour.type_pec"] = CSQLDataSource::prepareIn($type_pec);
  }
  if ($filter->_statut_pec) {
    if ($filter->_statut_pec == "attente") {
      $where["sejour.pec_accueil"] = " IS NULL";
      $where["sejour.entree_reelle"] = " IS NOT NULL";
    }
    elseif ($filter->_statut_pec == "en_cours") {
      $where["sejour.pec_accueil"] = " IS NOT NULL";
      $where["sejour.pec_service"] = " IS NULL";
    }
    elseif ($filter->_statut_pec == "termine") {
      $where["sejour.pec_service"] = " IS NOT NULL";
    }
  }
  if ($filter->praticien_id) {
    $user = CMediusers::get($filter->praticien_id);

    if ($user->isAnesth()) {
      $ljoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
      $ljoin['plagesop'] = 'plagesop.plageop_id = operations.plageop_id';
      $where[] = " operations.anesth_id = '$filter->praticien_id'
                   OR plagesop.anesth_id = '$filter->praticien_id' 
                   OR sejour.praticien_id = '$filter->praticien_id'";
    }
    else {
      $where['sejour.praticien_id'] = " = '$filter->praticien_id'";
    }
  }

  if ($period) {
    $hour = CAppUI::gconf("dPadmissions General hour_matin_soir");
    if ($period == "matin") {
      $where[] = "DATE_FORMAT(sejour.entree_prevue, '%H:%i:%s') <= '$hour' AND DATE_FORMAT(sejour.entree_prevue, '%H:%i:%s') >= '00:00:00'";
    }
    else {
      $where[] = "DATE_FORMAT(sejour.entree_prevue, '%H:%i:%s') > '$hour' AND DATE_FORMAT(sejour.entree_prevue, '%H:%i:%s') < '23:59:59'";
    }
  }

  $ljoin["patients"] = "sejour.patient_id = patients.patient_id";
  if ($order_col == "patient_id") {
    $order = "patients.nom $order_way, patients.prenom $order_way, sejour.entree_prevue";
  }
  elseif ($order_col == "entree_prevue") {
    $order = "sejour.entree_prevue $order_way, patients.nom, patients.prenom";
  }
  elseif ($order_col == "praticien_id") {
    unset($ljoin["patients"]);
    $ljoin["users"] = "sejour.praticien_id = users.user_id";
    $order = "users.user_last_name $order_way, users.user_first_name";
  }
  else {
    $order = "sejour.$order_col $order_way, patients.nom, patients.prenom, sejour.entree_prevue";
  }

  if ($date_interv_eg_entree) {
    if (!isset($ljoin["operations"])) {
      $ljoin['operations'] = 'sejour.sejour_id = operations.sejour_id';
    }
    $where[] = "operations.date = DATE(sejour.entree_prevue)";
  }

  $sejour = new CSejour();
  /** @var CSejour[] $sejours */
  $sejours = $sejour->loadList($where, $order, null, "sejour.sejour_id", $ljoin);

  $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadBackRefs($patients, "bmr_bhre");
  $affectations = CStoredObject::massLoadBackRefs($sejours, "affectations");
  CAffectation::massUpdateView($affectations);
  $praticiens = CStoredObject::massLoadFwdRef($sejours, "praticien_id");
  CStoredObject::massLoadFwdRef($praticiens, "function_id");
  foreach ($sejours as $_sejour) {
    $_sejour->loadRefPraticien();
    $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);
    $_sejour->loadRefFirstAffectation();
  }
}
if ($sejour_guid) {
  /* @var CSejour $sejour*/
  $sejour = CMbObject::loadFromGuid($sejour_guid);
  $sejour->loadRefPraticien();
  $sejour->loadRefPatient();
  $sejour->loadRefFirstAffectation()->updateView();
  $sejours[$sejour_guid] = $sejour;
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('filter'      , $filter);
$smarty->assign('services_ids', $services_ids);
$smarty->assign('sejours'     , $sejours);
$smarty->assign('period'      , $period);
$smarty->assign('order_col'   , $order_col);
$smarty->assign('order_way'   , $order_way);
$smarty->assign('prats'       , $prats);
$smarty->assign('enabled_service', $enabled_service);
if ($sejour_guid) {
  $smarty->assign('_sejour'   , $sejours[$sejour_guid]);
}

if ($sejour_guid) {
  $smarty->display("inc_accueil_patient_list.tpl");
}
elseif ($only_list) {
  $smarty->display("vw_accueil_patient_list.tpl");
}
else {
  $smarty->display("vw_accueil_patient.tpl");
}
