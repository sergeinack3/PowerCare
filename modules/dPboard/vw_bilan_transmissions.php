<?php
/**
 * @package Mediboard\Board
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * dPboard
 */
$user = CMediusers::get();

$ds       = CSQLDataSource::get("std");
$datetime = CMbDT::dateTime();
$date_max = $datetime;
$date_min = CMbDT::dateTime("-1 DAY", $date_max);

$praticien_id = CView::get("praticien_id", "ref class|CMediusers default|$user->_id");
CView::checkin();

// Chargement des praticiens
$mediuser   = new CMediusers();
$praticiens = $mediuser->loadPraticiens();

$board_access = CAppUI::pref("allow_other_users_board");
if ($user->isProfessionnelDeSante() && $board_access == 'only_me') {
  $praticiens = [$user->_id => $user];
}
elseif ($user->isProfessionnelDeSante() && $board_access == 'same_function') {
  $praticiens = $mediuser->loadPraticiens(PERM_READ, $user->function_id);
}
elseif ($user->isProfessionnelDeSante() && $board_access == 'write_right') {
  $praticiens = $mediuser->loadPraticiens(PERM_EDIT);
}

/* Chargement de la liste des sejours qui possedents des transmissions ou
   observations dans les dernieres 24 heures */

$sejour                         = new CSejour();
$sejours                        = array();
$where                          = array();
$ljoin["transmission_medicale"] = "transmission_medicale.sejour_id = sejour.sejour_id";
$ljoin["observation_medicale"]  = "observation_medicale.sejour_id = sejour.sejour_id";

$where[] = "(transmission_medicale.date BETWEEN '$date_min' and '$date_max') OR
  (observation_medicale.date BETWEEN '$date_min' and '$date_max')";

$where["sejour.praticien_id"] = " = '$praticien_id'";
/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, null, null, "sejour_id", $ljoin);

foreach ($sejours as $_sejour) {
  $_sejour->loadRefPatient();
}

// Variables de templates
$smarty = new CSmartyDP();
$smarty->assign("sejours", $sejours);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("praticien_id", $praticien_id);
$smarty->display("vw_bilan_transmissions");
