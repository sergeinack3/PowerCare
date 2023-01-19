<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\System\ViewSender\CSourceToViewSender;
use Ox\Mediboard\System\ViewSender\CViewSender;

CCanDo::checkEdit();

$sender_id = CValue::get("sender_id");

$source_to_vw_sender = new CSourceToViewSender();
$source_to_vw_sender->sender_id = $sender_id;

$view_sender = new CViewSender();
$view_sender->load($sender_id);
$view_sender->loadRefSendersSource();

$smarty = new CSmartyDP();
$smarty->assign("source_to_vw_sender", $source_to_vw_sender);
$smarty->assign("view_sender"        , $view_sender);
$smarty->display("inc_form_source_to_view_sender.tpl");
