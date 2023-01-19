<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();

$purge = CView::get("purge", "bool default|0");
$auto  = CView::get("auto" , "bool default|0");
$max   = CView::get("max"  , "num default|100");
CView::checkin();

$group = CGroups::loadCurrent();
$ljoin["operations"] = "plagesop.plageop_id = operations.plageop_id";
$ljoin["sallesbloc"] = "sallesbloc.salle_id = plagesop.salle_id";
$ljoin["bloc_operatoire"] = "bloc_operatoire.bloc_operatoire_id = sallesbloc.bloc_id";
$where["operations.operation_id"] = "IS NULL";
$where["bloc_operatoire.group_id"] = "= '$group->_id'";
$order = "plagesop.date";

$plage = new CPlageOp();
$success_count = 0;
$failures = array();
$plages = array();
if ($purge) {
  /** @var CPlageOp[] $plages */
  $plages = $plage->loadList($where, $order, $max, null, $ljoin);
  foreach ($plages as $_plage) {

    // Suppression des affectationde personnel
    if ($affectations = $_plage->loadAffectationsPersonnel()) {
      foreach ($affectations as $_affectations) {
        foreach ($_affectations as $_affectation) {
          $_affectation->delete();
        }
      }
    }

    if ($msg = $_plage->delete()) {
      $failures[$_plage->_id] = $msg;
      $_plage->loadRefSalle();
      continue;
    }

    $success_count++ ;
  }
}

$count = $plage->countList($where, null, $ljoin);

$smarty = new CSmartyDP;

$smarty->assign("plages", $plages);
$smarty->assign("purge", $purge);
$smarty->assign("max"  , $max);
$smarty->assign("auto" , $auto);
$smarty->assign("count", $count);
$smarty->assign("success_count", $success_count);
$smarty->assign("failures", $failures);

$smarty->display("purge_empty_plagesop.tpl");

