<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$guid = CValue::get("guid");
$page = CValue::get("page", 0);

//smarty
$smarty = new CSmartyDP();
$smarty->assign("guid", $guid);
$smarty->assign("page", $page);
$smarty->display("vw_list_medical_access.tpl");