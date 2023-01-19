<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CDiscipline;

/**
 * View disciplines
 */
CCanDo::checkRead();

$page   = intval(CValue::get('page'  , 0));
$filter = CValue::getOrSession("filter", "");

$step = 25;

$order = "text ASC";

$discipline = new CDiscipline();
if ($filter) {
  $disciplines       = $discipline->seek($filter, null, "$page, $step", true, null, $order);
  $total_disciplines = $discipline->_totalSeek;
}
else {
  $disciplines       = $discipline->loadList(null, $order, "$page, $step");
  $total_disciplines = $discipline->countList();
}

foreach ($disciplines as $_discipline) {
  $_discipline->loadGroupRefsBack();
}

$function_id = CValue::getOrSession("function_id");

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("discipline"       , $discipline);
$smarty->assign("disciplines"      , $disciplines);
$smarty->assign("total_disciplines", $total_disciplines);
$smarty->assign("page"             , $page);
$smarty->assign("step"             , $step);

$smarty->display("vw_list_disciplines.tpl");
