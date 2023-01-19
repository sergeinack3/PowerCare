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
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;

CCanDo::checkAdmin();
$purge = CView::get("purge", "bool default|0");
$max   = CView::get("max"  , "num default|100");
CView::checkin();

$group = CGroups::loadCurrent();
$ljoin = array();
$ljoin["sallesbloc"] = "`sallesbloc`.`bloc_id` = `bloc_operatoire`.`bloc_operatoire_id`";
$where = array();
$where["bloc_operatoire.group_id"] = "= '$group->_id'";
$where["sallesbloc.salle_id"] = "IS NULL";
$order = "bloc_operatoire.nom";

$bloc = new CBlocOperatoire();
$success_count = 0;
$failures = array();
$blocs = array();
if ($purge) {
  /** @var CBlocOperatoire[] $blocs */
  $blocs = $bloc->loadList($where, $order, $max, null, $ljoin);
  foreach ($blocs as $_bloc) {
    if ($msg = $_bloc->delete()) {
      $failures[$_bloc->_id] = $msg;
      continue;
    }
    $success_count++ ;
  }
}

$count = $bloc->countList($where, null, $ljoin);

$smarty = new CSmartyDP();

$smarty->assign("blocs", $blocs);
$smarty->assign("purge", $purge);
$smarty->assign("max"  , $max);
$smarty->assign("count", $count);
$smarty->assign("success_count" , $success_count);
$smarty->assign("failures"      , $failures);

$smarty->display("purge_empty_blocsop.tpl");