<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();
$offline = CView::get("offline", "bool");
$date    = CView::get("date", "date default|now", true);
CView::checkin();

// Chargement des rpu de la main courante
$sejour = new CSejour();

$where        = array();
$ljoin["rpu"] = "sejour.sejour_id = rpu.sejour_id";

// Par date
$date_tolerance = CAppUI::conf("dPurgences date_tolerance");
$date_before    = CMbDT::date("-$date_tolerance DAY", $date);
$date_after     = CMbDT::date("+1 DAY", $date);

// RPUs
$where[]                  = CAppUI::pref("showMissingRPU") ?
  "sejour.type " . CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence()) . " OR rpu.rpu_id IS NOT NULL" :
  "rpu.rpu_id IS NOT NULL";
$where["sejour.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";

$order = "sejour.entree ASC";

/** @var CSejour[] $sejours */
$sejours = array();

foreach (
  array(
    "sejour.entree BETWEEN '$date' AND '$date_after'",
    "sejour.sortie_reelle IS NULL AND sejour.entree BETWEEN '$date_before' AND '$date_after' AND sejour.annule = '0'",
    "sejour.sortie_reelle BETWEEN '$date' AND '$date_after'"
  ) as $_where
) {
  $where[100] = $_where;
  $sejours += $sejour->loadList($where, $order, null, "sejour.sejour_id", $ljoin);
}

$stats = array(
  "entree" => array(
    "total"        => 0,
    "less_than_1"  => 0,
    "more_than_75" => 0,
  ),
  "sortie" => array(
    "total"                    => 0,
    "transferts_count"         => 0,
    "mutations_count"          => 0,
    "etablissements_transfert" => array(),
    "services_mutation"        => array(),
  )
);

$offlines = array();

/** @var CPatient[] $patients */
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");
CPatient::massLoadIPP($patients);

CStoredObject::massLoadFwdRef($sejours, "praticien_id");
CStoredObject::massLoadFwdRef($sejours, "service_sortie_id");
CStoredObject::massLoadFwdRef($sejours, "etablissement_sortie_id");
CStoredObject::massLoadBackRefs($sejours, "rpu");

// Détail du chargement
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefRPU()->loadRefIDEResponsable();
  $_sejour->loadRefPraticien()->loadRefRemplacant($_sejour->entree);
  $_sejour->loadRefServiceMutation();
  $_sejour->loadRefEtablissementTransfert();

  $_sejour->_ref_rpu->loadRefSejourMutation();
  $_sejour->_ref_rpu->loadRefConsult();
  $_sejour->_veille = CMbDT::date($_sejour->entree) != $date;

  // Statistiques de sortie
  $stats["sortie"]["total"]++;

  // Statistiques de mutations de sejours
  $service_mutation = $_sejour->_ref_service_mutation;
  if ($service_mutation && $service_mutation->_id) {
    $stats["sortie"]["mutations_count"]++;
    $stat_service =& $stats["sortie"]["services_mutation"][$service_mutation->_id];
    if (!isset($stat_service)) {
      $stat_service = array(
        "ref"   => $service_mutation,
        "count" => 0
      );
    }
    $stat_service["count"]++;
  }

  // Statistiques de transferts de sejours
  $etablissement_tranfert = $_sejour->_ref_etablissement_transfert;
  if ($etablissement_tranfert && $etablissement_tranfert->_id) {
    $stats["sortie"]["transferts_count"]++;
    $stat_etablissement =& $stats["sortie"]["etablissements_transfert"][$etablissement_tranfert->_id];
    if (!isset($stat_etablissement)) {
      $stat_etablissement = array(
        "ref"   => $etablissement_tranfert,
        "count" => 0
      );
    }
    $stat_etablissement["count"]++;
  }

  // Statistiques d'entrée
  $stats["entree"]["total"]++;

  // Statistiques  d'âge de patient
  $patient =& $_sejour->_ref_patient;
  if ($patient->_annees < "1") {
    $stats["entree"]["less_than_1"]++;
  }

  if ($patient->_annees >= "75") {
    $stats["entree"]["more_than_75"]++;
  }

  // Chargement nécessaire du mode offline
  if ($offline) {
    $params                  = array(
      "rpu_id"  => $_sejour->_ref_rpu->_id,
      "dialog"  => 1,
      "offline" => 1,
    );
    $offlines[$_sejour->_id] = CApp::fetch("dPurgences", "print_dossier", $params);
  }
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("stats", $stats);
$smarty->assign("sejours", $sejours);
$smarty->assign("offline", $offline);
$smarty->assign("offlines", $offlines);

$smarty->display("print_main_courante");
