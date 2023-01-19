<?php
/**
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Files\CFileAddEdit;

CApp::setTimeLimit(600);
ignore_user_abort(1);

CValue::setSession(CValue::postOrSession("private"));
$_category_id = CValue::post('_category_id');

ini_set("upload_max_filesize", CAppUI::gconf("dPfiles General upload_max_filesize"));

$do = new CFileAddEdit();
$do->doBind();
if ((int)CValue::read($do->request, 'del')) {
  $do->doDelete();
}
else {
  $do->doStore();
}

if ($_category_id) {
  $do->_obj->_category_id = $_category_id;
}

$smarty = new CSmartyDP("modules/dPfiles");
/* Replacing the quotes with html entities because it can break the javascript calls in the template */
$smarty->assign("messages", str_replace("'", '&apos;', CAppUI::getMsg()));
$smarty->display("inc_callback_upload.tpl");

$do->doCallback();
