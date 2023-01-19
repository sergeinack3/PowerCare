<?php
/**
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Import\CExternalDBImport;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;

CCanDo::checkAdmin();

$import_class = CValue::get("import_class");

/** @var CExternalDBImport $obj */
$obj = new $import_class();
$list = $obj->getUsersList();

$smarty = new CSmartyDP("modules/importTools");
$smarty->assign("users", $list);
$smarty->assign("object", $obj);

$smarty->display("factory/vw_users.tpl");