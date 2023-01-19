<?php
/**
 * @package Mediboard\Board
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
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();
$filter            = new CConsultation();
$spec              = array(
  "date",
  "default" => CMbDT::date('-1 day')
);
$filter->_date_min = CView::get("_date_min", $spec, true);
$filter->_date_max = CView::get("_date_max", "date default|now", true);
$praticien_id      = CView::get("chir", "str", true);
$typeVue           = CView::get("typeVue", "str", true);
$bloc_id           = CView::get("bloc_id", "ref class|CBlocOperatoire", true);
$order             = CView::get('order', "str default|sortie_reelle", true);
CView::checkin();
CView::enableSlave();

$user = CMediusers::get();
// Chargement de la liste des praticiens
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

CStoredObject::massLoadFwdRef($praticiens, "function_id");

foreach ($praticiens as $_praticien) {
  $_praticien->loadRefFunction();
}

if (!$praticien_id && $user->isProfessionnelDeSante()) {
  $praticien_id = $user->_id;
}

$bloc  = new CBlocOperatoire();
$blocs = $bloc->loadGroupList();

// Variables de templates
$smarty = new CSmartyDP();
$smarty->assign("filter", $filter);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("praticien_id", $praticien_id);
$smarty->assign("blocs", $blocs);
$smarty->display("vw_bilan_actes_realises");
