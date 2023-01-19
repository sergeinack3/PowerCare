<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessage;

CCanDo::checkRead();

$usermessage_id = CView::getRefCheckRead("usermessage_id", "ref class|CUserMessage");

CView::checkin();

$user = CMediusers::get();

$usermessage = new CUserMessage();

if (!$usermessage->load($usermessage_id)) {
  CAppUI::stepAjax("CUserMessage-is_deleted", UI_MSG_WARNING);
  return;
}

$usermessage->loadRefsAttachments();
$usermessage->loadRefDests();
$usermessage->loadRefDestUser();
$usermessage->loadRefCreator()->loadRefFunction();
foreach ($usermessage->_ref_destinataires as $_destinataire) {
  $_destinataire->loadRefTo()->loadRefFunction();
}

$mode = "sentbox";
if ($usermessage->creator_id != $user->_id && $usermessage->_ref_dest_user->_id) {
  $mode = $usermessage->_ref_dest_user->archived ? "archive" : "inbox";

  if (!$usermessage->_ref_dest_user->datetime_read) {
    $usermessage->_ref_dest_user->datetime_read = CMbDT::dateTime();
    $usermessage->_ref_dest_user->store();
  }
}

if ($mode == "sentbox") {
  $usermessage->_ref_dest_user = reset($usermessage->_ref_destinataires);
}

$smarty = new CSmartyDP();
$smarty->assign("usermessage", $usermessage);
$smarty->assign("mode"       , $mode);
$smarty->display("inc_view_usermessage.tpl");
