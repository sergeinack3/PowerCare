<?php
/**
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClass;

CCanDo::checkEdit();

$ex_class_id = CValue::get("ex_class_id");

$ex_class = new CExClass();

$ex_class->load($ex_class_id);
$ex_class->getPermissions(true);

$smarty = new CSmartyDP();
$smarty->assign("ex_class", $ex_class);
$smarty->assign("all_types", CExClass::getAllPermTypes());
$smarty->display("inc_edit_ex_class_permissions.tpl");