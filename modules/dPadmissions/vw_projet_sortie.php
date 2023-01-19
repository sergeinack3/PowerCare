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
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Patients\CPatientHandicap;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$filter = new CSejour();
$patient = new CPatient();

$filter->praticien_id   = CValue::getOrSession("praticien_id");
$filter->_date_min_stat = CValue::getOrSession("_date_min_stat", CMbDT::date("- 1 month"));
$filter->_date_max_stat = CValue::getOrSession("_date_max_stat", CMbDT::date());
$patient->tutelle       = CValue::getOrSession("tutelle");
$handicap               = CValue::getOrSession("handicap");
$aide_organisee         = CValue::getOrSession("aide_organisee");
$see_sorties            = CValue::get("see_sorties", 0);
$mode_sortie            = CValue::getOrSession("mode_sortie");
$sejours_ids            = CValue::getOrSession("sejours_ids");

$sejours_ids  = CSejour::getTypeSejourIdsPref($sejours_ids);

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

$handicap_select = explode(",", $handicap);
CMbArray::removeValue("", $handicap_select);
$aide_select = explode(",", $aide_organisee);
$mode_sortie_select = explode(",", $mode_sortie);
CMbArray::removeValue("", $mode_sortie_select);

$list_mode_sortie = array();
if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
  $mode_sortie = new CModeSortieSejour();
  $where = array("actif" => "= '1'",);
  $list_mode_sortie = $mode_sortie->loadGroupList($where);
}

// Récupération de la liste des praticiens
$prat = CMediusers::get();
$prats = $prat->loadPraticiens();

$sejours = array();
if ($see_sorties) {
  $ljoin = array();
  $ljoin["operations"] = "operations.sejour_id = sejour.sejour_id";
  $where = array();

  if (count($type_pref)) {
    $where["sejour.type"] = CSQLDataSource::prepareIn($type_pref);
  }
  else {
    $where["sejour.type"] = CSQLDataSource::prepareNotIn(array_merge(CSejour::getTypesSejoursUrgence(), ["seances"]));
  }
  if ($filter->praticien_id) {
    $where["sejour.praticien_id"] = " = '$filter->praticien_id'";
  }
  if (count($handicap_select)) {
    $ljoin["patient_handicap"] = "sejour.patient_id = patient_handicap.patient_id";
    $where["patient_handicap.handicap"] = CSQLDataSource::prepareIn($handicap_select);
  }
  if (CAppUI::gconf("dPplanningOp CSejour show_aide_organisee") && count($aide_select) && !in_array('1', $aide_select)) {
    $prepared = count($aide_select) && in_array('', $aide_select) ? " OR sejour.aide_organisee IS NULL" : "";
    $where[] = "sejour.aide_organisee ".CSQLDataSource::prepareIn($aide_select).$prepared;
  }

  $where[] = "(operations.date BETWEEN '$filter->_date_min_stat' AND '$filter->_date_max_stat')";

  if (CAppUI::gconf("dPplanningOp CSejour show_tutelle")) {
    $ljoin["patients"]   = "patients.patient_id = sejour.patient_id";
    $where["patients.tutelle"] = " = '$patient->tutelle'";
  }

  if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie") && count($mode_sortie_select)) {
    $where["sejour.mode_sortie_id"] = CSQLDataSource::prepareIn($mode_sortie_select);
  }
  elseif (count($mode_sortie_select)) {
    $where["sejour.mode_sortie"] = CSQLDataSource::prepareIn($mode_sortie_select);
  }

  $order = "sejour.sortie_prevue";

  $sejour = new CSejour();
  $sejours = $sejour->loadGroupList($where, $order, null, "sejour.sejour_id", $ljoin);

  $patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CStoredObject::massLoadFwdRef($sejours, "praticien_id");
  CStoredObject::massLoadFwdRef($sejours, "mode_sortie_id");

  CStoredObject::massLoadBackRefs($patients, "bmr_bhre");

  foreach ($sejours as $_sejour) {
    /* @var CSejour $_sejour*/
    $_sejour->loadRefPatient()->updateBMRBHReStatus($_sejour);
    $_sejour->loadRefPraticien();
    $_sejour->loadRefModeSortie();
    $_sejour->loadRefCurrAffectation();
    $_sejour->loadRefLastOperation();
    $_sejour->_ref_patient->loadRefsPatientHandicaps();
  }

  CMbArray::pluckSort($sejours, SORT_ASC, '_ref_patient', 'nom');
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign('filter'            , $filter);
$smarty->assign('patient'           , $patient);
$smarty->assign('patient_handicap'  , new CPatientHandicap());
$smarty->assign('sejours'           , $sejours);
$smarty->assign('handicap_select'   , $handicap_select);
$smarty->assign('aide_select'       , $aide_select);
$smarty->assign('prats'             , $prats);
$smarty->assign('list_mode_sortie'  , $list_mode_sortie);
$smarty->assign('mode_sortie_select', $mode_sortie_select);

if ($see_sorties) {
  $smarty->display("inc_vw_projet_sortie.tpl");
}
else {
  $smarty->display("vw_projet_sortie.tpl");
}
