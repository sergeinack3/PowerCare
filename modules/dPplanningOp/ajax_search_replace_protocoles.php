<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Medicament\CMedicament;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkAdmin();

$dm_id    = CView::get("dm_id", "ref class|CDM");
$code_cip = CView::get("code_cip", "str");

CView::checkin();

$bdm = CMedicament::getBase();

$protocole_op = new CProtocoleOperatoire();

$ds = CSQLDataSource::get("std");

$where = [
  "operation_id" => "IS NULL"
];

$ljoin = [
  "materiel_operatoire" => "materiel_operatoire.protocole_operatoire_id = protocole_operatoire.protocole_operatoire_id"
];

if ($dm_id) {
  $where["dm_id"] = $ds->prepare("= ?", $dm_id);
}
elseif ($code_cip) {
  $where["code_cip"] = $ds->prepare("= ?", $code_cip);
  $where["bdm"]      = $ds->prepare("= ?", $bdm);
}

$protocoles_op = $protocole_op->loadList($where, null, null, "protocole_operatoire.protocole_operatoire_id", $ljoin);

CStoredObject::massLoadFwdRef($protocoles_op, "chir_id");
CStoredObject::massLoadFwdRef($protocoles_op, "function_id");
CStoredObject::massLoadFwdRef($protocoles_op, "group_id");

foreach ($protocoles_op as $_protocole_op) {
  $_protocole_op->loadRefChir();
  $_protocole_op->loadRefFunction();
  $_protocole_op->loadRefGroup();
}

$smarty = new CSmartyDP();

$smarty->assign("protocoles_op", $protocoles_op);

$smarty->display("inc_search_replace_protocoles");
