<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CColorLibelleSejour;
use Ox\Mediboard\PlanningOp\CSejour;

global $m;

CCanDo::checkRead();

CApp::setMemoryLimit("768M");

$date = CView::get("date", "date default|now");

CView::checkin();
CView::enforceSlave();

// Chargement des sejours SSR pour la date courante
$group_id  = CGroups::loadCurrent()->_id;
$date_time = $date . " " . CMbDT::time();
$where     = array(
  "type"     => "= '$m'",
  "group_id" => "= '$group_id'",
  "annule"   => "= '0'"
);

// Masquer les services inactifs
$service             = new CService();
$service->group_id   = $group_id;
$service->cancelled  = "1";
$services            = $service->loadMatchingList();
$where["service_id"] = CSQLDataSource::prepareNotIn(array_keys($services));

$sejours = CSejour::loadListForDate($date, $where);

CStoredObject::massLoadFwdRef($sejours, "praticien_id");
$patients = CStoredObject::massLoadFwdRef($sejours, "patient_id");

CStoredObject::massLoadBackRefs($sejours, "bilan_ssr");
CStoredObject::massLoadBackRefs($sejours, "notes");
CSejour::massLoadCurrAffectation($sejours, $date_time);

CSejour::massLoadNDA($sejours);
CPatient::massLoadIPP($patients);

$plannings = array();

// Chargement du détail des séjour
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPraticien();

  // Bilan SSR
  $bilan = $_sejour->loadRefBilanSSR();
  $bilan->loadRefKineJournee($date);
  $bilan->loadRefPraticienDemandeur();

  // Détail du séjour
  $_sejour->checkDaysRelative($date);
  $_sejour->loadRefsNotes();

  // Patient
  $patient = $_sejour->loadRefPatient();

  // Prescription
  if ($prescription = $_sejour->loadRefPrescriptionSejour()) {
    $prescription->loadRefsLinesElementByCat();
    if (HandlerManager::isObjectHandlerActive('CPrescriptionAlerteHandler')) {
      $prescription->_count_alertes = $prescription->countAlertsNotHandled("medium");
    }
    else {
      $prescription->countFastRecentModif();
    }
  }

  // Chargement du planning du sejour
  $args_planning              = array();
  $args_planning["sejour_id"] = $_sejour->_id;
  $args_planning["large"]     = 1;
  $args_planning["print"]     = 1;
  $args_planning["height"]    = 600;
  $args_planning["date"]      = $date;

  // Chargement du planning de technicien
  $plannings[$_sejour->_id] = CApp::fetch("ssr", "ajax_planning_sejour", $args_planning);
}

// Couleurs
$colors = CColorLibelleSejour::loadAllFor(CMbArray::pluck($sejours, "libelle"));

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("sejours", $sejours);
$smarty->assign("colors", $colors);
$smarty->assign("date", $date);
$smarty->assign("order_col", "");
$smarty->assign("order_way", "");
$smarty->assign("plannings", $plannings);

$smarty->display("offline_sejours");
