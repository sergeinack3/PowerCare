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
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessage;
use Ox\Mediboard\Messagerie\CUserMessageAttachment;
use Ox\Mediboard\Messagerie\CUserMessageDest;
use Ox\Mediboard\Messagerie\CUserMessageDestGroup;

CCanDo::checkEdit();

$user         = CMediusers::get();
$date         =  CMbDT::dateTime();
$dests        = CView::post("dest", ['str', 'default' => []]);
$functions    = CView::post('function', ['str', 'default' => []]);
$groups       = CView::post('group', ['str', 'default' => []]);
$del          = CView::post("del", 'bool default|0');
$send_it      = CView::post("_send", 'bool');
$archive_mine = CView::post("_archive", 'bool');
$read_only    = CView::post("_readonly", 'bool');
$callback     = CView::post('callback', 'str');
$files        = CValue::files('formfile');

CView::checkin();

$usermessage = new CUserMessage();

// edit mode (draft)
$usermessage->load($_POST["usermessage_id"]);
if ($del && $usermessage->_id) {
  if ($msg = $usermessage->delete()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
  $msg = 'CUserMessage-msg-delete';
  $message_id = null;
}
else {
  $usermessage->bind($_POST);
  if ($msg = $usermessage->store()) {
    CAppUI::stepAjax($msg, UI_MSG_ERROR);
  }
  $message_id = $usermessage->usermessage_id;

  if ($files) {
    $files_data = array();

    foreach ($files as $field => $values) {
      foreach ($values as $index => $value) {
        if (!array_key_exists($index, $files_data)) {
          $files_data[$index] = array();
        }

        $files_data[$index][$field] = $value;
      }
    }

    foreach ($files_data as $file) {
      $attachment = new CUserMessageAttachment();
      $attachment->user_message_id = $message_id;
      $attachment->store();

      if ($msg = $attachment->setFile($file, true)) {
        CAppUI::setMsg(CAppUI::tr('CUserMessageAttachment-error-upload-file') . ':' . CAppUI::tr($msg), UI_MSG_ERROR);
      }

      if ($msg = $attachment->store()) {
        CAppUI::setMsg($msg, UI_MSG_ERROR);
      }
    }
  }

  $destinataires = $usermessage->loadRefDests();
  foreach ($destinataires as $_dest) {

    // mine reception
    if ($_dest->to_user_id == $user->_id) {
      $_dest->archived = $archive_mine;
      if (!$_dest->datetime_read) {
        $_dest->datetime_read = $date;
      }
      if ($msg = $_dest->store()) {
        CAppUI::stepAjax($msg, UI_MSG_ERROR);
      }
    }

    // in edit mode, we don't find a dest, (delete it !)
    if (!$read_only && !in_array($_dest->to_user_id, $dests)) {
      if ($msg = $_dest->delete()) {
        CAppUI::stepAjax($msg, UI_MSG_ERROR);
      }
      continue;
    }
  }

  foreach ($functions as $function_id) {
    $function = CFunctions::loadFromGuid("CFunctions-$function_id");
    foreach ($function->loadRefsUsers() as $user) {
      if (!in_array($user->_id, $dests)) {
        $dests[] = $user->_id;
      }
    }
  }

  foreach ($groups as $group_id) {
    $group = CUserMessageDestGroup::findOrNew($group_id);
    $group->loadUsersLinks();
    foreach ($group->_user_links as $link) {
      if (!in_array($link->user_id, $dests)) {
        $dests[] = $link->user_id;
      }
    }
  }

  foreach ($dests as $_dest) {
    $destinataire = new CUserMessageDest();
    $destinataire->user_message_id = $usermessage->_id;
    $destinataire->from_user_id = $usermessage->creator_id;
    $destinataire->to_user_id = $_dest;
    $destinataire->loadMatchingObject();
    if ($send_it) {
      $destinataire->datetime_sent = $date;
    }
    if ($msg = $destinataire->store()) {
      CAppUI::stepAjax($msg, UI_MSG_ERROR);
    }
  }

  $msg = $_POST["usermessage_id"] ? 'CUserMessage-msg-modify' : 'CUserMessage-msg-create';
  if ($send_it) {
    $msg = 'CUserMessage-msg-sent';
  }
}

CAppUI::setMsg($msg, UI_MSG_OK);

$smarty = new CSmartyDP;
$messages = CAppUI::getMsg();
$smarty->assign('messages', $messages);

$smarty->display('inc_callback_modal.tpl');

if($callback) {
  CAppUI::callbackAjax($callback, 'internal', $message_id);
}