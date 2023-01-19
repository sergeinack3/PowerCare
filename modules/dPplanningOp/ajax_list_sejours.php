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
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

$patient_id         = CView::get("patient_id", "ref class|CPatient");
$sejour_id          = CView::get("sejour_id", "ref class|CSejour");
$check_collision    = CView::get("check_collision", "bool");
$date_entree_prevue = CView::get("date_entree_prevue", "date");
$hour_entree_prevue = CView::get("hour_entree_prevue", "num");
$min_entree_prevue  = CView::get("min_entree_prevue", "num");
$date_sortie_prevue = CView::get("date_sortie_prevue", "date");
$hour_sortie_prevue = CView::get("hour_sortie_prevue", "num");
$min_sortie_prevue  = CView::get("min_sortie_prevue", "num");
$limit              = CView::get("limit", "num");

CView::checkin();

$collision_sejour = null;

$patient = new CPatient();

if (!$patient->load($patient_id)) {
  CAppUI::stepMessage(UI_MSG_WARNING, "Patient '%s' inexistant", $patient_id);
  return;
}

$patient->loadRefsSejours([], $limit);

$hours_sejour_proche = CAppUI::conf("dPplanningOp CSejour hours_sejour_proche");

$date = $date_entree_prevue;
$date .= " ".str_pad($hour_entree_prevue, 2, "0", STR_PAD_LEFT);
$date .= ":".str_pad($min_entree_prevue, 2, "0", STR_PAD_LEFT);
$date .= ":00";

CSejour::massLoadNDA($patient->_ref_sejours);
CStoredObject::massLoadFwdRef($patient->_ref_sejours, "praticien_id");

foreach ($patient->_ref_sejours as $_sejour) {
  // Séjours proches
  if ($_sejour->sortie) {
    $_date = CMbDT::dateTime("+$hours_sejour_proche HOUR", $_sejour->sortie);
    if ($_date > $date && $date > $_sejour->sortie) {
      $_sejour->_is_proche = 1;
    }
  }
  $_sejour->loadRefPraticien();
  $_sejour->loadRefEtablissement();
}

if ($check_collision) {
  $sejour = new CSejour();
  
  if (!$sejour->load($sejour_id)) {
    $sejour->patient_id = $patient_id;
    $sejour->group_id = CGroups::loadCurrent()->_id;
  }

  CAccessMedicalData::logAccess($sejour);

  // Simulation du formulaire
  $sejour->_date_entree_prevue = $date_entree_prevue;
  $sejour->_date_sortie_prevue = $date_sortie_prevue;
  $sejour->_hour_entree_prevue = $hour_entree_prevue;
  $sejour->_hour_sortie_prevue = $hour_sortie_prevue;
  $sejour->_min_entree_prevue  = $min_entree_prevue;
  $sejour->_min_sortie_prevue  = $min_sortie_prevue;
  $sejour->updatePlainFields();

  // Calcul des collisions potentielles
  $sejours_collides = $sejour->getCollisions();
  foreach ($patient->_ref_sejours as $_sejour) {
    if (array_key_exists($_sejour->_id, $sejours_collides)) {
      $collision_sejour = $_sejour->_id;
      break;
    }
  }
}

$sejours_by_NDA = array();

foreach ($patient->_ref_sejours as $_sejour) {
  if (!isset($sejours_by_NDA[$_sejour->_NDA])) {
    $sejours_by_NDA[$_sejour->_NDA] = array();
  }

  $sejours_by_NDA[$_sejour->_NDA][$_sejour->_id] = $_sejour;
}

$smarty = new CSmartyDP();

$smarty->assign("patient"         , $patient);
$smarty->assign("sejours_by_NDA"  , $sejours_by_NDA);
$smarty->assign("collision_sejour", $collision_sejour);
$smarty->assign("selected_guid"   , "CSejour-$sejour_id");

$smarty->display("inc_list_sejours");
