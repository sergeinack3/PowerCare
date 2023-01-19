<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocole;

CCanDo::checkEdit();
$chir_id     = CView::get("chir_id", "num", true);
$function_id = CView::get("function_id", "ref class|CFunctions");
$libelle     = CView::get("libelle", "str");
$page        = CView::get("page", "num default|0");

CView::checkin();

$libelle = trim($libelle);
if (strlen($libelle) < 3) {
  $libelle = "";
}

$step = 30;

if (!$chir_id && !$function_id && !$libelle && $chir_id !== 0) {
  CAppUI::stepMessage(UI_MSG_OK, CAppUI::tr("CProtocole-_field_controle"));
  CApp::rip();
}

$protocole = new CProtocole();

$where = array();
$type = null;
if ($chir_id) {
  $chir = CMediusers::get($chir_id);
  $chir->loadRefFunction();
  $functions = array($chir->function_id);
  foreach ($chir->loadBackRefs("secondary_functions") as $curr_sec_func) {
    $functions[] = $curr_sec_func->function_id;
  }
  $list_functions = implode(",", $functions);
  $where[] = "protocole.chir_id = '$chir->_id' OR protocole.function_id IN ($list_functions)";
  $type = "chir";
}
elseif ($function_id) {
  $where["function_id"] = "= '$function_id'";
  $type = "function";
}
elseif ($libelle) {
  $where["libelle"] = "LIKE '%". addslashes($libelle) . "%'";
  $type = "libelle";
}
elseif ($chir_id === 0) {
  $list_prats = $list_functions = array();

  $mediuser = new CMediusers();
  foreach ($mediuser->loadPraticiens(PERM_READ) as $_prat) {
    $_prat->countProtocoles();
    if ($_prat->_count_protocoles) {
      $list_prats[$_prat->_id] = $_prat->_id;
    }
  }
  $where[] = "protocole.chir_id ".CSQLDataSource::prepareIn(array_keys($list_prats));
}

$protocoles = $protocole->loadListWithPerms(PERM_EDIT, $where, "libelle_sejour, libelle, codes_ccam");
if ($chir_id === 0) {
  $protocoles = $protocole->loadListWithPerms(PERM_EDIT, $where, "libelle_sejour, libelle, codes_ccam");
  $order_view = CMbArray::pluck($protocoles, "_ref_chir", "_view");
  array_multisort($order_view, SORT_ASC, $protocoles);
}

$protocoles = CProtocole::computeMedian($protocoles);
$count_protocoles = count($protocoles);
$protocoles = array_slice($protocoles, $page, $step);

$protocoles_by_type = array();
/** @var CProtocole $_protocole */
foreach ($protocoles as $_protocole) {
  $_protocole->loadRefChir();
  $_protocole->loadRefFUnction();
  if ($chir_id === 0) {
    $type = $_protocole->_ref_chir->_guid;
  }

  $protocoles_by_type[$type][$_protocole->_id] = $_protocole;
}

$smarty = new CSmartyDP();

$smarty->assign("protocoles_by_type", $protocoles_by_type);
$smarty->assign("page"      , $page);
$smarty->assign("count"     , $count_protocoles);
$smarty->assign("step"      , $step);
$smarty->assign("libelle"   , $libelle);

$smarty->display("inc_list_protocoles_controle");