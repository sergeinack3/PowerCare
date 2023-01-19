<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkAdmin();

$chir_id                 = CView::get("chir_id", "ref class|CMediusers");
$function_id             = CView::get("function_id", "ref class|CFunctions");
$protocole_op_id         = CView::get("protocole_op_id", "ref class|CProtocoleOperatoire");
$offline                 = CView::get("offline", "bool default|0");
$search_all_protocole_op = CView::get("search_all_protocole_op", "bool default|1");

CView::checkin();

$protocole_op = new CProtocoleOperatoire();

$ds = $protocole_op->getDS();

$curr_group = CGroups::loadCurrent();

$where = [
  "actif" => "= '1'"
];

if ($protocole_op_id) {
  $where["protocole_operatoire_id"] = $ds->prepare("= ?", $protocole_op_id);
}
elseif ($offline || $search_all_protocole_op) {
  $where_user = [
    "functions_mediboard.group_id" => $ds->prepare("= ?", $curr_group->_id)
  ];

  $ljoin_user = [
    "functions_mediboard" => "functions_mediboard.function_id = users_mediboard.function_id"
  ];
  $users_ids  = (new CMediusers())->loadIds($where_user, null, null, null, $ljoin_user);

  $functions_ids = (new CFunctions())->loadIds(["group_id" => $ds->prepare("= ?", $curr_group->_id)]);

  $where[] = "chir_id " . CSQLDataSource::prepareIn($users_ids)
    . " OR function_id " . CSQLDataSource::prepareIn($functions_ids);
}
else {
  if ($chir_id) {
    $where["chir_id"] = $ds->prepare("= ?", $chir_id);
  }
  elseif ($function_id) {
    $where["function_id"] = $ds->prepare("= ?", $function_id);
  }
}

$protocoles_op = count($where) ? $protocole_op->loadList($where) : [];

if (!count($protocoles_op)) {
  CAppUI::stepAjax("CProtocoleOperatoire.none");

  return;
}

CMbArray::pluckSort($protocoles_op, SORT_ASC, 'libelle');

CStoredObject::massLoadFwdRef($protocoles_op, 'chir_id');
CStoredObject::massLoadFwdRef($protocoles_op, 'function_id');
CStoredObject::massLoadFwdRef($protocoles_op, 'group_id');

foreach ($protocoles_op as $_protocole_operatoire) {
    $_protocole_operatoire->loadRefChir();
    $_protocole_operatoire->loadRefFunction();
    $_protocole_operatoire->loadRefGroup();
    $_protocole_operatoire->loadRefsMaterielsOperatoires(true);
}

$smarty = new CSmartyDP();
$smarty->assign('protocoles_op', $protocoles_op);
$smarty->assign('offline', $offline);
$smarty->display('print_protocoles_op');
