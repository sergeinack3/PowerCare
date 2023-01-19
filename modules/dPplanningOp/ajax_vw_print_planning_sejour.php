<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CPlanificationSysteme;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;

CCanDo::checkRead();
$sejour_guid = CView::get("sejour_guid", "guid class|CSejour");
CView::checkin();

/** @var CSejour $sejour */
$sejour = CMbObject::loadFromGuid($sejour_guid);

CAccessMedicalData::logAccess($sejour);

$debut = CMbDT::date($sejour->entree);
$fin   = CMbDT::date($sejour->sortie);

$patient = $sejour->loadRefPatient();
$patient->loadRefLatestConstantes(null, array("poids", "taille"));
$patient->loadRefPhotoIdentite();

$dossier_medical = $patient->loadRefDossierMedical();
if ($dossier_medical->_id) {
  $dossier_medical->loadRefsAllergies();
  $dossier_medical->loadRefsAntecedents();
  $dossier_medical->countAntecedents();
  $dossier_medical->countAllergies();
}

$ljoin                 = array();
$ljoin['plageconsult'] = "consultation.plageconsult_id = plageconsult.plageconsult_id";

$where               = array();
$where[]             = "plageconsult.date BETWEEN '$debut' AND '$fin'";
$where["patient_id"] = " = '$patient->_id'";

$consultation  = new CConsultation();
$consultations = $consultation->loadList($where, null, null, "consultation_id", $ljoin);

$plages_consult = CStoredObject::massLoadFwdRef($consultations, "plageconsult_id");
CStoredObject::massLoadFwdRef($plages_consult, "chir_id");

foreach ($consultations as $_consult) {
  /* @var CConsultation $_consult */
  $_consult->loadRefPlageConsult()->loadRefChir();
}

$ljoin             = array();
$ljoin['plagesop'] = "plagesop.plageop_id = operations.plageop_id";

$where                         = array();
$where[]                       = "(plagesop.date BETWEEN '$debut' AND '$fin') OR (operations.date BETWEEN '$debut' AND '$fin')";
$where["operations.sejour_id"] = " = '$sejour->_id'";

$operation  = new COperation();
$operations = $operation->loadList($where, null, null, "operation_id", $ljoin);

CStoredObject::massLoadFwdRef($operations, "chir_id");
CStoredObject::massLoadFwdRef($operations, "plageop_id");

foreach ($operations as $_operation) {
  /* @var COperation $_operation */
  $_operation->loadRefChir();
  $_operation->loadRefPlageOp();

  if ($_operation->getActeExecution() == $_operation->_datetime) {
    $_operation->_acte_execution = CMbDT::addDateTime($_operation->temp_operation, $_operation->_datetime);
  }
}

$dates             = array($debut, $fin);
$prescription      = $sejour->loadRefPrescriptionSejour();
$lines["imagerie"] = $prescription->loadRefsLinesElement(null, "imagerie");
$lines["kine"]     = $prescription->loadRefsLinesElement(null, "kine");
$lines["consult"]  = $prescription->loadRefsLinesElement(null, "consult");

$lines_elements = array();
$lines_counter  = 0;

foreach ($lines as $category => $cat) {
  foreach ($cat as $_line) {
    /* @var CPrescriptionLineElement $_line */
    $replanifications = array();
    $_line->loadRefsAdministrations($dates);
    $_line->loadRefPraticien();

    $order_date = CMbArray::pluck($_line->_ref_administrations, "dateTime");
    array_multisort($order_date, SORT_DESC, $_line->_ref_administrations);

    foreach ($_line->_ref_administrations as $_admin) {
      if (!$_admin->planification) {
        continue;
      }
      $replanifications[$_admin->object_class . "-" . $_admin->object_id]["$_admin->original_dateTime"] = 1;
      $libelle = $_admin->quantite . " " . $_line->_unite_prise . " - " . $_line->_view;

      $lines_elements[$category][$_admin->_id]['object']   = $_admin;
      $lines_elements[$category][$_admin->_id]['label']    = $libelle;
      $lines_elements[$category][$_admin->_id]['datetime'] = $_admin->dateTime;
      $lines_elements[$category][$_admin->_id]['duration'] = 60;

      $lines_counter++;
    }

    // Chargement des planifications pour la date courante
    $planif                = new CPlanificationSysteme();
    $where                 = array();
    $where["object_id"]    = " = '$_line->_id'";
    $where["object_class"] = " = '$_line->_class'";
    $where["dateTime"]     = " BETWEEN '$debut 00:00:00' AND '$fin 23:59:59'";

    $planifs = $planif->loadList($where, "dateTime DESC");

    $order_date = CMbArray::pluck($planifs, "dateTime");
    array_multisort($order_date, SORT_DESC, $planifs);

    foreach ($planifs as $_planif) {
      /* @var CPlanificationSysteme $_planif */
      $object_guid = $_planif->object_class . "-" . $_planif->object_id;
      if (isset($replanifications[$object_guid]) && isset($replanifications[$object_guid][$_planif->dateTime])) {
        continue;
      }
      /* @var CPlanificationSysteme $_planif */
      $_planif->loadRefPrise();
      $libelle = $_planif->_ref_prise->quantite . " " . $_line->_unite_prise . " - " . $_line->_view;

      $lines_elements[$category][$_line->_id]['object']   = $_line;
      $lines_elements[$category][$_line->_id]['label']    = $libelle;
      $lines_elements[$category][$_line->_id]['datetime'] = $_planif->dateTime;
      $lines_elements[$category][$_line->_id]['duration'] = 60;

      $lines_counter++;
    }
  }
}

//Ajout des RDV externes dans le planning du séjour
$rdv_externes = $sejour->loadRefsRDVExternes();

$smarty = new CSmartyDP();
$smarty->assign("sejour"       , $sejour);
$smarty->assign("consultations", $consultations);
$smarty->assign("operations"   , $operations);
$smarty->assign("lines"        , $lines_elements);
$smarty->assign("lines_counter", $lines_counter);
$smarty->assign("debut"        , $debut);
$smarty->assign("fin"          , $fin);
$smarty->assign("rdv_externes" , $rdv_externes);
$smarty->display("vw_print_planning_sejour");
