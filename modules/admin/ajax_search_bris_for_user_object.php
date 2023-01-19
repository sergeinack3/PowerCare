<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CBrisDeGlace;

$date_start   = CValue::getOrSession("date_start", CMbDT::date());
$date_end     = CValue::getOrSession("date_end", $date_start);
$object_class = CValue::get("object_class");
$user_id      = CValue::get("user_id");

$briss = CBrisDeGlace::loadBrisForOwnObject($user_id, $date_start, $date_end);
foreach ($briss as $_bris) {
  $_bris->loadRefUser()->loadRefFunction();
  $_bris->loadTargetObject();
}

//smarty
$smarty = new CSmartyDP();
$smarty->assign("bris", $briss);
$smarty->display("inc_search_bris_by_user.tpl");