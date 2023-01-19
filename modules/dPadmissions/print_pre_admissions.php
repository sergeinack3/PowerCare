<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Interop\Dmp\CDMP;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$sejour = new CSejour();

// Initialisation de variables
$sejour_prepared = CView::get("sejour_prepared", "bool", true);
$date            = CView::get("date", "date default|now", true);
$filter          = CView::get("filter", "str");
$spec_type_pec = array(
  "str",
  "default" => $sejour->_specs["type_pec"]->_list
);
$type_pec        = CView::get("type_pec", $spec_type_pec);
$period          = CView::get("period", "enum list|matin|soir");

CView::checkin();

$listByPrat = array();

$date_min = CMbDT::dateTime("00:00:00", $date);
$date_max = CMbDT::dateTime("23:59:59", $date);

if ($period) {
  $hour = CAppUI::gconf("dPadmissions General hour_matin_soir");
  if ($period == "matin") {
    // Matin
    $date_max = CMbDT::dateTime($hour, $date);
  }
  else {
    // Soir
    $date_min = CMbDT::dateTime($hour, $date);
  }
}

// Récupération de la liste des anesthésistes
$mediuser = new CMediusers();
$anesthesistes = $mediuser->loadAnesthesistes(PERM_READ);

$consult = new CConsultation();

// Récupération des consultation préanesthésique du jour
$ljoin = array();
$ljoin["plageconsult"] = "consultation.plageconsult_id = plageconsult.plageconsult_id";
$ljoin["patients"]     = "consultation.patient_id = patients.patient_id";
$ljoin["sejour"]       = "sejour.sejour_id = consultation.sejour_id";

$where = array();
$where["consultation.patient_id"] = "IS NOT NULL";
$where["consultation.annule"] = "= '0'";
$where["plageconsult.chir_id"] = CSQLDataSource::prepareIn(array_keys($anesthesistes));
$where["plageconsult.date"] = "= '$date'";

/** @var CConsultation[] $listConsultations */
$listConsultations = $consult->loadList($where, null, null, null, $ljoin);
$dossiers_anesth = CStoredObject::massLoadBackRefs($listConsultations, "consult_anesth");

// Optimisation des chargements
CStoredObject::massLoadFwdRef($listConsultations, "patient_id");
CStoredObject::massLoadFwdRef($listConsultations, "plageconsult_id");

foreach ($listConsultations as $_consult) {
  $dossiers_anesth_consult = $_consult->loadRefsDossiersAnesth();

  if (!count($dossiers_anesth_consult)) {
    unset($listConsultations[$_consult->_id]);
    continue;
  }

  $_consult->loadRefPlageConsult();

  if ($_consult->_datetime > $date_max || $_consult->_datetime < $date_min) {
    unset($listConsultations[$_consult->_id]);
    continue;
  }

  $_consult->loadRefPatient();

  if (CModule::getActive("appFineClient")) {
    CAppFineClient::loadIdex($_consult->_ref_patient, CGroups::loadCurrent()->_id);
    $_consult->_ref_patient->loadRefStatusPatientUser();
  }

  $_consult->_ref_chir->loadRefFunction();
}

$operations = CStoredObject::massLoadFwdRef($dossiers_anesth, "operation_id");
CStoredObject::massLoadFwdRef($dossiers_anesth, "sejour_id");
CStoredObject::massLoadFwdRef($operations, "plageop_id");
CStoredObject::massLoadFwdRef($operations, "sejour_id");

/** @var CSejour[] $sejours_total */
$sejours_total = array();

foreach ($listConsultations as $_consult) {
  $_consult->checkDHE($date);
  $dossier_empty = false;
  foreach ($_consult->_refs_dossiers_anesth as $_dossier) {
    $_sejour = $_dossier->_ref_sejour;
    $_sejour->loadRefsOperations();
    if ($_sejour->_id) {
      if (!in_array($_sejour->type_pec, $type_pec)) {
        unset($listConsultations[$_consult->_id]);
        continue;
      }
      $sejours_total[$_sejour->_id] = $_sejour;
    }
    else {
      $dossier_empty = true;
    }
  }
  $_consult->_next_sejour_and_operation = null;
  if ($dossier_empty) {
    $next = $_consult->_ref_patient->getNextSejourAndOperation($_consult->_ref_plageconsult->date);

    if ($next["COperation"]->_id) {
      $next["COperation"]->loadRefSejour();
      $next["COperation"]->_ref_sejour->loadRefPraticien();
      $next["COperation"]->_ref_sejour->loadNDA();
      $next["COperation"]->_ref_sejour->loadRefsNotes();
      if ($filter == "dhe") {
        unset($listConsultations[$_consult->_id]);
      }
    }
    if ($next["CSejour"]->_id) {
      $next["CSejour"]->loadRefPraticien();
      $next["CSejour"]->loadNDA();
      $next["CSejour"]->loadRefsNotes();
      if ($filter == "dhe") {
        unset($listConsultations[$_consult->_id]);
      }
    }
    $_consult->_next_sejour_and_operation = $next;
  }
  elseif ($filter == "dhe") {
    unset($listConsultations[$_consult->_id]);
  }

  $curr_prat = $_sejour->praticien_id;
  if (!isset($listByPrat[$curr_prat])) {
    $listByPrat[$curr_prat]["praticien"] = $_sejour->_ref_praticien;
  }
  $listByPrat[$curr_prat]["consult"][] = $_consult;
}

//Ajout des pré-admissions préparées sans consultation préanesthésique de créée
if ($sejour_prepared) {
  $ljoin = array();
  $ljoin["operations"]           = "operations.sejour_id = sejour.sejour_id";
  $ljoin["consultation_anesth"]  = "consultation_anesth.sejour_id = sejour.sejour_id OR consultation_anesth.operation_id = operations.operation_id";
  $where = array();
  if (count($sejours_total)) {
    $where["sejour.sejour_id"] = CSQLDataSource::prepareNotIn(array_keys($sejours_total));
  }
  if (count($type_pec)) {
    $where["sejour.type_pec"] = CSQLDataSource::prepareIn($type_pec);
  }
  $where["sejour.entree_preparee"] = " = '1'";
  $where["sejour.entree_preparee_date"] = "BETWEEN '$date 00:00:00' AND '$date 23:59:00'";
  $where["consultation_anesth.consultation_anesth_id"] = " IS NULL";
  $sejour = new CSejour();
  $sejours = $sejour->loadList($where, null, null, null, $ljoin);
  CStoredObject::massLoadFwdRef($sejours, "patient_id");
  CSejour::massLoadNDA($sejours);
  /* @var CSejour[] $sejours*/
  foreach ($sejours as $_sejour) {
    $_sejour->loadRefPraticien();
    $_sejour->loadRefsNotes();
    $consult_anesth = new CConsultAnesth();
    $consult_anesth->_ref_sejour = $_sejour;
    $consult_sejour = new CConsultation();
    $consult_sejour->_ref_plageconsult = new CPlageconsult();
    $consult_sejour->_refs_dossiers_anesth[] = $consult_anesth;
    $patient = $_sejour->loadRefPatient();
    $patient->countINS();
    $consult_sejour->_ref_patient = $patient;
    $consult_sejour->_next_sejour_and_operation["CSejour"] = $_sejour;
    $consult_sejour->_next_sejour_and_operation["COperation"] = new COperation();
  }
}

$patients = CStoredObject::massLoadFwdRef($sejours_total, "patient_id");
CStoredObject::massLoadFwdRef($sejours_total, "praticien_id");
CStoredObject::massLoadBackRefs($sejours_total, "notes");
$affectations = CStoredObject::massLoadBackRefs($sejours_total, "affectations", "sortie DESC");
CAffectation::massUpdateView($affectations);

if (CModule::getActive("dmp")) {
    CStoredObject::massLoadBackRefs($patients, "state_dmp");
}

// Chargement des NDA
CSejour::massLoadNDA($sejours_total);

// Chargement optimisé des prestations
CSejour::massCountPrestationSouhaitees($sejours_total);

foreach ($sejours_total as $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefPraticien();
  $_sejour->loadRefsNotes();
  $_sejour->loadRefPrestation();
  $_sejour->loadRefsAffectations();
  $_sejour->getDroitsC2S();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("date"             , $date);
$smarty->assign("listConsultations", $listConsultations);
$smarty->assign("total", count($listConsultations));
$smarty->assign('sejour_prepared'  , $sejour_prepared);
$smarty->assign("sejour"           , new CSejour());
$smarty->assign("type_pec"         , $type_pec);
$smarty->assign("period"           , $period);

$smarty->display("print_pre_admissions.tpl");
