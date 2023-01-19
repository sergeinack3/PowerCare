<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CPlageSeanceCollective;

global $g, $m;

$plage_id  = CView::get("plage_id", "ref class|CPlageSeanceCollective", true);
$order_way = CView::get("order_way", "enum list|ASC|DESC default|ASC", true);
$order_col = CView::get("order_col", "enum list|patient_id|lit_id default|patient_id", true);
CView::checkin();

$plage = new CPlageSeanceCollective();
$plage->load($plage_id);
$plage->loadRefElementPrescription();
$date = CMbDT::date("$plage->day_week this week");
if ($date < CMbDT::date()) {
  $date = CMbDT::date("$plage->day_week next week");
}
$plage_debut = $date . " " . $plage->debut;
$plage_fin   = $date . " " . CMbDT::time("+$plage->duree MINUTES", $plage->debut);

$ljoin                                                      = array();
$ljoin["prescription"]                                      = "prescription.object_id = sejour.sejour_id AND prescription.object_class = 'CSejour'";
$ljoin["prescription_line_element"]                         = "prescription_line_element.prescription_id = prescription.prescription_id";
$where                                                      = array();
$where["prescription.type"]                                 = " = 'sejour'";
$where["prescription_line_element.element_prescription_id"] = " = '$plage->element_prescription_id'";
$where["sejour.entree"]                                     = " <= '$date 23:59:59'";
$where["sejour.sortie"]                                     = " >= '$date 00:00:00'";
$where["sejour.annule"]                                     = " = '0'";

$sejour  = new CSejour();
$sejours = $sejour->loadList($where, "entree asc", null, "sejour.sejour_id", $ljoin);

//Evénements déjà planifiés sur cette plage
$where                              = array();
$where[]                            = "sejour_id " . CSQLDataSource::prepareIn(array_keys($sejours));
$where[]                            = "DATE(evenement_ssr.debut) >= '" . CMbDT::date() . "'";
$where[]                            = "DAYNAME(evenement_ssr.debut) = '" . $plage->day_week . "'";
$where[]                            = "evenement_ssr.plage_id <> '$plage_id' OR evenement_ssr.plage_id IS NULL";
$evenement                          = new CEvenementSSR();
$evenements                         = $evenement->loadList($where);

$debut = CMbDT::time($plage->debut);
$fin   = CMbDT::time("+$plage->duree minutes", $plage->debut);

//Récupération de la liste des séjours étant planifiés dans cette plage à partir de cette semaine
$sejours_affectes = $plage->loadRefsSejoursAffectes();

// Récupération des séjours en collision
$sejours_collisions = array();
//Retrait des séjours ayant des événements déjà planifiés sur cette plage
foreach ($evenements as $_evenement) {
  if ($_evenement->_heure_deb < $fin && $_evenement->_heure_fin > $debut) {
    $_plage         = $_evenement->loadRefPlageSeanceCollective();
    $_evenement_fin = CMbDT::dateTime("+$_evenement->_duree minutes", $_evenement->debut);
    if ($_evenement_fin > $plage_debut && $_evenement->debut < $plage_fin && $_plage->_id && $_plage->niveau > $plage->niveau) {
      $sejours_collisions[$_evenement->sejour_id] = $_evenement->sejour_id;
    }
  }
}

// Suppression des séjours qui ne peuvent pas ajouter d'événements ssr dans cette plage
$now               = CMbDT::date();
$first_day_of_week = CMbDT::date("$plage->day_week this week");
if ($now > $first_day_of_week) {
  $first_day_of_week = CMbDT::date("+1 week", $first_day_of_week);
}

foreach ($sejours as $_sejour) {
  if (in_array($_sejour->_id, $sejours_affectes)) {
    continue;
  }
  $date_sortie         = CMbDT::date($_sejour->sortie);
  $date_entree         = CMbDT::date($_sejour->entree);
  $first_day_of_sejour = $first_day_of_week > $date_entree ? $first_day_of_week : $date_entree;
  $days                = array();
  for ($day = $first_day_of_sejour; $day < $date_sortie; $day = CMbDT::date("+1 week", $day)) {
    $days[$day] = $day;
  }
  $unset_id = count($days) > 0;
  foreach ($days as $_day) {
    $evt_collision        = new CEvenementSSR();
    $evt_collision->debut = $_day . " " . $plage->debut;
    $evt_collision->duree = $plage->duree;
    if (count($evt_collision->getCollectivesCollisions(null, $plage->niveau, $_sejour->_id, "<", false)) === 0) {
      $unset_id = false;
      break;
    }
  }

  if ($unset_id) {
    unset($sejours[$_sejour->_id]);
  }
}

CMbObject::massLoadFwdRef($sejours, "patient_id");
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
}

if ($order_col == "patient_id") {
    $order_nom = CMbArray::pluck($sejours, "_ref_patient", "nom");
    $order_prenom = CMbArray::pluck($sejours, "_ref_patient", "prenom");
  array_multisort(
    $order_nom, constant("SORT_$order_way"),
    $order_prenom, constant("SORT_$order_way"),
    $sejours
  );
}
else {
    $order =  CMbArray::pluck($sejours, "entree");
  array_multisort($order, constant("SORT_$order_way"), $sejours);
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("sejours", $sejours);
$smarty->assign("sejours_affectes", $sejours_affectes);
$smarty->assign("sejours_collisions", $sejours_collisions);
$smarty->assign("plage", $plage);
$smarty->assign("order_way", $order_way);
$smarty->assign("order_col", $order_col);

$smarty->display("vw_patients_plage_collective");
