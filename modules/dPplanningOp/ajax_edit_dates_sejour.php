<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

$operation_id       = CValue::get("operation_id");
$sejour_id          = CValue::get("sejour_id");
$date_move          = CValue::get("date_move");
$callback           = CValue::get("callback");
$entree_prevue      = CValue::get("entree_prevue");
$sortie_prevue      = CValue::get("sortie_prevue");
$new_sejour         = CValue::get("new_sejour");
$hour_intervention  = CValue::get("hour_intervention");
$duree              = CValue::get("duree");               //days
$duree_hours        = $duree*24;                          // hours

if ($operation_id) {
  $operation = new COperation();
  $operation->load($operation_id);

  CAccessMedicalData::logAccess($operation);

  $sejour = $operation->loadRefSejour();
}
else {
  $sejour = new CSejour();
  $sejour->load($sejour_id);

  CAccessMedicalData::logAccess($sejour);

  if ($new_sejour) {
    $sejour->_id = null;
    $entree_prevue = CMbDT::date($date_move) . " " . $hour_intervention;
    $sortie_prevue = CMbDT::addDateTime($duree_hours, $entree_prevue);
  }
}

if (!$date_move) {
  $date_move = "$operation->date $operation->time_operation";
}

if ($entree_prevue && $sortie_prevue) {
  $sejour->entree_prevue = $entree_prevue;
  $sejour->sortie_prevue = $sortie_prevue;
}

if (isset($operation)) {
  $nb_days = CMbDT::daysRelative("$operation->date $operation->time_operation", $date_move);
}
else {
  $nb_days = CMbDT::daysRelative($sejour->entree_prevue, $entree_prevue);
}

if ($nb_days > 0 ) {
  $sejour->entree_prevue = CMbDT::dateTime("+$nb_days day", $sejour->entree_prevue);
  $sejour->sortie_prevue = CMbDT::dateTime("+$nb_days day", $sejour->sortie_prevue);
}
else {
  $sejour->entree_prevue = CMbDT::dateTime("$nb_days day", $sejour->entree_prevue);
  $sejour->sortie_prevue = CMbDT::dateTime("$nb_days day", $sejour->sortie_prevue);
}


$smarty = new CSmartyDP;

$smarty->assign("sejour"   , $sejour);
$smarty->assign("date_move", $date_move);
$smarty->assign("callback" , $callback);

$smarty->display("inc_edit_dates_sejour");
