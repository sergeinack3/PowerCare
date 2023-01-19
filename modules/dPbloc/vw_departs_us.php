<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;

/**
 * dPbloc
 */
$date_depart = CValue::getOrSession("date_depart", CMbDT::dateTime());
$bloc_id     = CValue::getOrSession("bloc_id");
$order_way   = CValue::getOrSession("order_way", "_heure_us");
$order_col   = CValue::getOrSession("order_col", "ASC");

$bloc = new CBlocOperatoire();
$where["group_id"] = " = '".CGroups::loadCurrent()->_id."'";
$where["actif"]    = " = '1'";
$blocs = $bloc->loadListWithPerms(PERM_READ, $where, "nom");

$smarty = new CSmartyDP;

$smarty->assign("date_depart", $date_depart);
$smarty->assign("blocs"      , $blocs);
$smarty->assign("bloc_id"    , $bloc_id);
$smarty->assign("order_col"  , $order_col);
$smarty->assign("order_way"  , $order_way);

$smarty->display("vw_departs_us.tpl");
