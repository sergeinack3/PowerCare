<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\PlanSoins\CAdministration;
use Ox\Mediboard\PlanSoins\CPlanificationSysteme;
use Ox\Mediboard\Prescription\CPrescriptionLineElement;
use Ox\Mediboard\Soins\CRDVExterne;
use Ox\Mediboard\System\CPlanningEvent;
use Ox\Mediboard\System\CPlanningMonth;
use Ox\Mediboard\System\CPlanningWeek;

CCanDo::checkRead();
$sejour_id     = CView::get("sejour_id", "ref class|CSejour");
$debut         = CView::get("debut", "date");
$planning_type = CView::get("planning_type", "enum list|week|month default|month", true);
$refresh       = CView::get("refresh", "bool default|0");
CView::checkin();

$isMonth = ($planning_type === "month");

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$entree = $sejour->entree;

$start = ($debut) ? $debut : $entree;

if ($isMonth) {
  $debut = (new DateTime($start))->modify("first day of this month")->format("Y-m-d");
  $fin   = (new DateTime($start))->modify("last day of this month")->format("Y-m-d");
  $start = (new DateTime($start))->format("Y-m-d");
}
else {
  $debut = (new DateTime($start))->modify("monday this week")->format("Y-m-d");
  $fin   = (new DateTime($start))->modify("sunday this week")->format("Y-m-d");
}

$nbjours = 7;

$patient = $sejour->loadRefPatient();
// Chargement des caracteristiques du patient
$patient =& $sejour->_ref_patient;
$patient->loadRefPhotoIdentite();
$patient->loadRefLatestConstantes(null, ["poids", "taille"]);

$dossier_medical = $patient->loadRefDossierMedical();
if ($dossier_medical->_id) {
  $dossier_medical->loadRefsAllergies();
  $dossier_medical->loadRefsAntecedents();
  $dossier_medical->countAntecedents();
  $dossier_medical->countAllergies();
}

$sejour->loadRefCurrAffectation($debut)->updateView();
if (!$sejour->_ref_curr_affectation->_id) {
  $sejour->loadRefsAffectations();
  $sejour->_ref_curr_affectation = $sejour->_ref_last_affectation;
  $sejour->_ref_curr_affectation->updateView();
}

//Instanciation du planning
$default_length = 30;
if ($isMonth) {
  $planning        = new CPlanningMonth($start, $debut, $fin);
  $planning->title = "";
  $planning->guid  = $sejour->_guid;
}
else {
  $planning           = new CPlanningWeek($start, $debut, $fin, $nbjours, false, 450, null, true);
  $planning->hour_min = "07";
  $planning->hour_max = "20";
  $planning->pauses   = ["12"];
}

$planning->title = "";
$planning->guid  = $sejour->_guid;

// Add entries and exists
foreach (["entree_prevue", "entree_reelle", "sortie_prevue", "sortie_reelle"] as $_item) {
  if ($sejour->$_item) {
    $date = new DateTime($sejour->$_item);

    $libelle = CAppUI::tr("CSejour._horodatage." . $_item) . " " . CAppUI::tr("at") . " " . $date->format("H\hi");

    $event   = new CPlanningEvent(
      $sejour->_guid,
      $sejour->$_item,
      $default_length,
      $libelle,
      "#ff5c5c"
    );
    $event->onmousover = true;
    $event->setObject($sejour);
    $planning->addEvent($event);
  }
}


// Add appointments
$ljoin = ['plageconsult' => "consultation.plageconsult_id = plageconsult.plageconsult_id"];
$where = ["plageconsult.date" => "BETWEEN '$debut' AND '$fin'",
          "patient_id"        => "= '$patient->_id'"];

$consultation  = new CConsultation();
$consultations = $consultation->loadList($where, null, null, "consultation_id", $ljoin);

foreach ($consultations as $_consult) {
  /* @var CConsultation $_consult */
  $_consult->loadRefPlageConsult()->loadRefChir();

  $libelle = $_consult->_view . " - " . $_consult->_ref_plageconsult->_ref_chir->_view;
  $length  = (!$isMonth) ? CMbDT::minutesRelative($_consult->_datetime, $_consult->_date_fin) : $default_length;

  $event             = new CPlanningEvent($_consult->_guid, $_consult->_datetime, $length, $libelle, "#cfc", true);
  $event->setObject($_consult);
  $event->onmousover = true;
  $planning->addEvent($event);
}


// Add operations
$ljoin = ['plagesop' => "plagesop.plageop_id = operations.plageop_id"];
$where = ["(plagesop.date BETWEEN '$debut' AND '$fin') OR (operations.date BETWEEN '$debut' AND '$fin')",
  "operations.sejour_id" => "= '$sejour_id'"];

$operation  = new COperation();
$operations = $operation->loadList($where, null, null, "operation_id", $ljoin);

foreach ($operations as $_operation) {
  /* @var COperation $_operation */
  $_operation->loadRefChir();
  $_operation->loadRefPlageOp();

  if ($_operation->getActeExecution() == $_operation->_datetime) {
    $_operation->_acte_execution = CMbDT::addDateTime($_operation->temp_operation, $_operation->_datetime);
  }

  $libelle = "Intervention par le Dr " . $_operation->_ref_chir->_view . " - $_operation->libelle";
  $length  = (!$isMonth) ? CMbDT::minutesRelative($_operation->_datetime, $_operation->_acte_execution) : $default_length;

  $event             = new CPlanningEvent($_operation->_guid, $_operation->_datetime, $length, $libelle, "#fcc", true);
  $event->setObject($_operation);
  $event->onmousover = true;
  $planning->addEvent($event);
}

$dates        = [$debut, $fin];
$prescription = $sejour->loadRefPrescriptionSejour();
$lines        = ["imagerie" => $prescription->loadRefsLinesElement(null, "imagerie"),
                 "kine"     => $prescription->loadRefsLinesElement(null, "kine"),
                 "consult"  => $prescription->loadRefsLinesElement(null, "consult")];
$color        = null;

foreach ($lines as $category => $cat) {
  // Set colors for each category
  switch ($category) {
    case "kine":
      $color = "#ccf";
      break;
    case "consult":
      $color = "#ffeeee";
      break;
    default:
      $color = "#aaa";
  }

  foreach ($cat as $_line) {
    /* @var CPrescriptionLineElement $_line */
    $replanifications = [];
    $_line->loadRefsAdministrations($dates);
    foreach ($_line->_ref_administrations as $_admin) {
      if (!$_admin->planification) {
        continue;
      }
      $replanifications[$_admin->object_class . "-" . $_admin->object_id]["$_admin->original_dateTime"] = 1;
      /* @var CAdministration $_admin */
      $libelle           = $_admin->quantite . " " . $_line->_unite_prise . " - " . $_line->_view;
      $event             = new CPlanningEvent($_admin->_guid, $_admin->dateTime, 60, $libelle, $color, true);
      $event->setObject($_admin);
      $event->onmousover = true;
      $planning->addEvent($event);
    }

    // Chargement des planifications pour la date courante
    $where = ["object_id"    => "= '$_line->_id'",
              "object_class" => "= '$_line->_class'",
              "dateTime"     => "BETWEEN '$debut 00:00:00' AND '$fin 23:59:59'"];

    $planif  = new CPlanificationSysteme();
    $planifs = $planif->loadList($where, "dateTime");
    foreach ($planifs as $_planif) {
      /* @var CPlanificationSysteme $_planif */
      $object_guid = $_planif->object_class . "-" . $_planif->object_id;
      if (isset($replanifications[$object_guid]) && isset($replanifications[$object_guid][$_planif->dateTime])) {
        continue;
      }
      /* @var CPlanificationSysteme $_planif */
      $_planif->loadRefPrise();
      $libelle           = $_planif->_ref_prise->quantite . " " . $_line->_unite_prise . " - " . $_line->_view;
      $event             = new CPlanningEvent($_line->_guid, $_planif->dateTime, 60, $libelle, $color, true);
      $event->setObject($_line);
      $event->onmousover = true;
      $planning->addEvent($event);
    }
  }
}

//Ajout des RDV externes dans le planning du séjour
$rdv_externes = $sejour->loadRefsRDVExternes();

foreach ($rdv_externes as $_rdv) {
  /* @var CRDVExterne $_rdv */
  $color     = "#c5c5c5";
  $className = "";

  if ($_rdv->statut == 'annule') {
    $className = "hatching";
    $color     = "#fff";
  }

  $event             = new CPlanningEvent($_rdv->_guid, $_rdv->date_debut, $_rdv->duree, $_rdv->libelle, $color, true, $className);
  $event->setObject($_rdv);
  $event->onmousover = true;
  $planning->addEvent($event);
}

$smarty = new CSmartyDP();
$smarty->assign("sejour", $sejour);
$smarty->assign("planning", $planning);
$smarty->assign("isMonth", $isMonth);
$smarty->assign("planning_type", $planning_type);
$smarty->assign("debut", $debut);
$smarty->assign("fin", $fin);
$smarty->assign("precedent", CMbDT::date("-1 " . $planning_type, $debut));
$smarty->assign("suivant", CMbDT::date("+1 " . $planning_type, $debut));

if ($refresh) {
  $smarty->display("inc_planning_sejour");
}
else {
  $smarty->display("vw_planning_sejour");
}
