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
use Ox\Mediboard\System\Forms\CExClassMessage;
use Ox\Mediboard\System\Forms\CExObject;

CCanDo::checkEdit();

$ex_message_id = CValue::get("ex_message_id");
$ex_group_id   = CValue::get("ex_group_id");

CExObject::$_locales_cache_enabled = false;
$ex_message                        = new CExClassMessage();

if ($ex_message->load($ex_message_id)) {
  $ex_message->loadRefsNotes();
  $ex_message->loadRefsHyperTextLink();
}
else {
  $ex_message->ex_group_id = $ex_group_id;
}

$ex_message->loadRefPredicate()->loadView();
$ex_message->loadRefExGroup()->loadRefExClass();
$ex_message->loadRefProperties();

$smarty = new CSmartyDP();
$smarty->assign("ex_message", $ex_message);
$smarty->display("inc_edit_ex_message.tpl");