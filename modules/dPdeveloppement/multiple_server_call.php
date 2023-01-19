<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

$get_session  = CValue::getOrSession("multiple_server_call_get");
$post_session = CValue::getOrSession("multiple_server_call_post");

$smarty = new CSmartyDP();
$smarty->assign("get", $get_session);
$smarty->assign("post", $post_session);
$smarty->display("multiple_server_call.tpl");