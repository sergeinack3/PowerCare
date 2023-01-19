<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

//CCanDo::checkRead();

use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Mediusers\CMediusers;

$date_start = CValue::getOrSession("date_start", CMbDT::date());
$date_end   = CValue::getOrSession("date_end", CMbDT::date());
$user_id    = CValue::get("user_id");
$user       = CMediusers::get($user_id);

//smarty
$smarty = new CSmartyDP();
$smarty->assign("user", $user);
$smarty->assign("date_start", $date_start);
$smarty->assign("date_end", $date_end);
$smarty->display("inc_list_bris_de_glace.tpl");