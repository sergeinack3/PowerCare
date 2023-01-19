<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// Récupération des  paramètres
use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CChambre;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Urgences\CRPUReservationBox;

$chambre_id = CView::get("chambre_id", "ref class|CChambre");
$rpu_id     = CView::get("rpu_id", "ref class|CRPU");
$patient_id = CView::get("patient_id", "ref class|CPatient");
$vue_hospi  = CView::get("vue_hospi", "bool default|0");
$is_mater   = CView::get("is_mater", "bool default|0");
$date       = CView::get("date", "date default|now", true);
CView::checkin();

$chambre = new CChambre();
$chambre->load($chambre_id);
$chambre->loadRefsLits();

$patient = new CPatient();
$patient->load($patient_id);

$affectations     = array();
$reservations_box = array();
$sumbit_only      = false;
$nb_lits          = 0;
$rpu              = null;
$q                = "";
foreach ($chambre->_ref_lits as $lit) {
  if ($nb_lits) {
    $q .= " OR ";
  }
  if ($vue_hospi) {
    $q .= "lit_id = '" . $lit->_id . "'";
  }
  else {
    $q .= "rpu.box_id = '" . $lit->_id . "'";
  }
  $nb_lits++;
}
$nb_lit_dispo = $nb_lits;
//Si on se trouve dans le module hospi
if ($vue_hospi) {
  $date_min = CMbDT::dateTime($date);
  $date_max = CMbDT::dateTime("+1 day", $date_min);

  $affectation     = new CAffectation();
  $where["entree"] = "<= '$date_max'";
  $where["sortie"] = ">= '$date_min'";
  $where[]         = $q;

  $affs = $affectation->loadList($where);
  foreach ($affs as $_aff) {
    $affectations[$_aff->lit_id] = "1";
  }
}
//Si on vient du module urgences
else {
  $date = CMbDT::dateTime();

  $ljoin                    = array();
  $ljoin["sejour"]          = "rpu.sejour_id = sejour.sejour_id";
  $where                    = array();
  $where["sejour.type"]     = CSQLDataSource::prepareIn(CSejour::getTypesSejoursUrgence());
  $where[]                  = "'$date' BETWEEN entree AND sortie";
  $where["sejour.annule"]   = " = '0'";
  $where["sejour.group_id"] = "= '" . CGroups::loadCurrent()->_id . "'";

  $where[] = $q;
  $rpu     = new CRPU();
  $rpus    = $rpu->loadList($where, null, null, null, $ljoin);
  foreach ($rpus as $_rpu) {
    $affectations[$_rpu->box_id] = "1";
  }
  if (CAppUI::gconf("dPurgences Placement use_reservation_box")) {
    $rpu->load($rpu_id);
    $rpu->loadRefBox()->loadRefChambre();
    $rpu->loadRefReservation()->loadRefLit()->loadRefChambre();
    $reservations_box = CRPUReservationBox::loadCurrentReservations($rpu_id);
    $lit_dispo        = $chambre->_ref_lits;
    foreach ($lit_dispo as $lit) {
      if (isset($reservations_box[$lit->_id])) {
        unset($lit_dispo[$lit->_id]);
      }
    }
    $nb_lit_dispo = count($lit_dispo);
    if ($rpu->_ref_reservation->_id) {
      $first_lit = reset($lit_dispo);
      if (count($lit_dispo) == 1 && $rpu->_ref_reservation->lit_id == $first_lit->lit_id) {
        $sumbit_only = true;
      }
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("chambre"         , $chambre);
$smarty->assign("patient"         , $patient);
$smarty->assign("affectations"    , $affectations);
$smarty->assign("vue_hospi"       , $vue_hospi);
$smarty->assign("is_mater"        , $is_mater);
$smarty->assign("rpu"             , $rpu);
$smarty->assign("reservations_box", $reservations_box);
$smarty->assign("sumbit_only"     , $sumbit_only);
$smarty->assign("nb_lit_dispo"    , $nb_lit_dispo);

$smarty->display("inc_choice_lit");
