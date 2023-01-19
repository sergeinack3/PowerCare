<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CMbRange;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Personnel\CPlageConge;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CBilanSSR;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CReplacement;

CCanDo::checkRead();
$sejour_id = CView::get("sejour_id", "ref class|CSejour", true);
$conge_id  = CView::get("plage_id", "str", true);
$type      = CView::get("type", "enum list|kine|reeducateur", true);
$date      = CView::get("date", "date default|now", true);
CView::checkin();

// Standard plage
$conge = new CPlageConge();

// Week dates
$monday = CMbDT::date("last monday", CMbDT::date("+1 DAY", $date));
$sunday = CMbDT::date("next sunday", CMbDT::date("-1 DAY", $date));

// Pseudo plage for user activity
if (preg_match("/[deb|fin][\W][\d]+/", $conge_id)) {
  list($activite, $user_id) = explode("-", $conge_id);
  $limit = $activite == "deb" ? $monday : $sunday;
  $conge = CPlageConge::makePseudoPlage($user_id, $activite, $limit);
}
else {
  $conge->load($conge_id);
}

$date_min = max($monday, $conge->date_debut);
$date_max = CMbDT::date("+1 DAY", min($sunday, $conge->date_fin));

// Séjour unique
if ($sejour_id) {
  // Chargement du séjour 
  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  $sejours[$sejour->_id] = $sejour;

  // Chargement d'un remplacement possible
  $replacement = $sejour->loadRefReplacement($conge_id);
  if ($replacement->_id) {
    $replacement->loadRefsNotes();
    $replacement->loadRefReplacer()->loadRefFunction();
  }
}
// Tous les séjours de la plage
else {
  // Chargement des séjours
  $sejours  = CBilanSSR::loadSejoursSurConges($conge, $monday, $sunday);
  $patients = CMbObject::massLoadFwdRef($sejours, "patient_id");

  // Pas de remplacement pour une collection de séjours
  $replacement = new CReplacement();
}

// Chargement des praticiens
$user = new CMediusers();
$user->load($conge->user_id);
$user->loadRefFunction();

// Séjours des patients
$therapeutes = array();
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
  $_sejour->loadRefBilanSSR()->loadRefTechnicien();
  $therapeutes += CEvenementSSR::getAllTherapeutes($_sejour->patient_id, $user->function_id);
}

// Chargement des comptes d'événements
$evenements_counts  = array();
$evenement          = new CEvenementSSR();
$where["debut"]     = " BETWEEN '$date_min' AND '$date_max'";
$where["sejour_id"] = CSQLDataSource::prepareIn(array_keys($sejours));
$keys_therapeutes   = array_keys($therapeutes);
/** @var CEvenementSSR $_evenement */
foreach ($evenement->loadList($where, null, null, "evenement_ssr.evenement_ssr_id") as $_evenement) {
  if (in_array($_evenement->therapeute_id, $keys_therapeutes)) {
    @$evenements_counts[$_evenement->sejour_id][$_evenement->therapeute_id]++;
  }
}

if (!$replacement->_id) {
  $replacement->conge_id  = $conge_id;
  $replacement->sejour_id = $sejour_id;
}

$transfer_count  = 0;
$transfer_counts = array();

// Transfer event count
if ($type == 'kine') {
  $date_min = $conge->date_debut;
  $date_max = CMbDT::date("+1 DAY", $conge->date_fin);
  foreach ($sejours as $_sejour) {
    $bilan                  = $_sejour->loadRefBilanSSR();
    $tech                   = $bilan->loadRefTechnicien();
    $where                  = array();
    $where["sejour_id"]     = " = '$_sejour->_id'";
    $where["therapeute_id"] = " = '$tech->kine_id'";
    $where["debut"]         = "BETWEEN '$date_min' AND '$date_max'";
    $transfer_count         += $evenement->countList($where);
  }
}

// Transfer event counts
if ($type == "reeducateur") {
  $where                  = array();
  $where["sejour_id"]     = " = '$sejour->_id'";
  $where["therapeute_id"] = " = '$conge->user_id'";

  foreach (range(0, 6) as $weekday) {
    $day = CMbDT::date("+$weekday DAYS", $monday);
    if (!CMbRange::in($day . " " . $conge->date_debut, $date_min, $date_max)) {
      $transfer_counts[$day] = 0;
      continue;
    }
    $where["debut"] = "BETWEEN '$day' AND '" . CMbDT::date("+1 DAY", $day) . "'";
    $count                                = $evenement->countList($where);
    $transfer_counts[$day]                = $count;
    $transfer_count                       += $count;
  }
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("evenements_counts", $evenements_counts);
$smarty->assign("sejours", $sejours);
$smarty->assign("therapeutes", $therapeutes);
$smarty->assign("transfer_count", $transfer_count);
$smarty->assign("transfer_counts", $transfer_counts);
$smarty->assign("sejour", reset($sejours));
$smarty->assign("sejour_id", $sejour_id);
$smarty->assign("replacement", $replacement);
$smarty->assign("conge", $conge);
$smarty->assign("user", $user);
$smarty->assign("type", $type);
$smarty->display("inc_vw_replacement");
