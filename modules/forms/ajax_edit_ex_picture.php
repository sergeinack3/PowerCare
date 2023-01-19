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
use Ox\Mediboard\Forms\CExClassPicture;
use Ox\Mediboard\System\Forms\CExClassFieldGroup;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$ex_picture_id = CValue::get("ex_picture_id");
$ex_group_id   = CValue::get("ex_group_id");

CExObject::$_locales_cache_enabled = false;

$ex_group = new CExClassFieldGroup();
$ex_group->load($ex_group_id);

$ex_picture = new CExClassPicture();

if ($ex_picture->load($ex_picture_id)) {
  $ex_picture->loadRefsNotes();
}
else {
  $ex_picture->ex_group_id = $ex_group_id;
  $ex_picture->disabled    = "0";
}

$ex_picture->loadRefPredicate()->loadView();
$ex_picture->loadRefTriggeredExClass();
$ex_picture->loadRefExClass();
$ex_picture->loadRefFile();

$smarty = new CSmartyDP();
$smarty->assign("ex_picture", $ex_picture);
$smarty->assign("ex_group", $ex_group);
$smarty->display("inc_edit_ex_picture.tpl");