<?php
/**
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

global $m;

CCanDo::checkRead();
$user = CMediusers::get();

$praticien_id      = CValue::getOrSession("prat_bilan_id", $user->_id);
$signee            = CValue::getOrSession("signee", 0);         // par default les non signees
$date_min          = CValue::getOrSession("_date_entree_prevue", CMbDT::date());  // par default, date du jour
$date_max          = CValue::getOrSession("_date_sortie_prevue", CMbDT::date());
$type_prescription = CValue::getOrSession("type_prescription", "sejour");  // sejour - externe - sortie_manquante

// Chargement de la liste des praticiens
$mediuser   = new CMediusers();
$praticiens = $mediuser->loadPraticiens();

/* Handle the list of mediusers for the view displayed in the board module */
if ($m === 'dPboard') {
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
}

if (!$praticien_id && $user->isPraticien()) {
  $praticien_id = $user->_id;
}

$sejour                      = new CSejour();
$sejour->_date_entree_prevue = $date_min;
$sejour->_date_sortie_prevue = $date_max;

// Smarty template
$smarty = new CSmartyDP();
$smarty->assign("praticiens", $praticiens);
$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("signee", $signee);
$smarty->assign("sejour", $sejour);
$smarty->assign("type_prescription", $type_prescription);
$smarty->display('vw_bilan_prescription');