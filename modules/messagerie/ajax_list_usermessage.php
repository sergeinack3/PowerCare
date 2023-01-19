<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMessage;
use Ox\Mediboard\Messagerie\CUserMessageDest;

CCanDo::checkRead();

$user_id = CView::getRefCheckRead("user_id", 'ref class|CMediusers');
$user = CMediusers::get($user_id);
$mode = CView::get("mode", "str default|inbox");
$page = CView::get("page", 'num default|0');
$step = CAppUI::pref("nbMailList");

CView::checkin();

$usermessage = new CUserMessage();
$group = null;
$where = array();
$lj = array("usermessage_dest" => "usermessage.usermessage_id = usermessage_dest.user_message_id");
$order = "usermessage_dest.datetime_sent DESC";
$unread = 0;

if ($mode == "inbox") {
  $where = array();
  $where["usermessage_dest.to_user_id"] = " = '$user->_id'";
  $where["datetime_sent"] = " IS NOT NULL";
  $where["usermessage_dest.archived"] = " = '0'";
  $where['usermessage_dest.deleted'] = " = '0'";
  $order = "usermessage_dest.starred DESC, usermessage_dest.datetime_sent DESC";

  $where["datetime_read"] = " IS NULL";
  $unread = $usermessage->countList($where, $group, $lj);
  $unread = $unread ? $unread : 0;
  unset($where["datetime_read"]);
  $total_found = CUserMessageDest::countInboxFor($user);
}

if ($mode == "archive") {
  $where = array();
  $where["usermessage_dest.to_user_id"] = " = '$user->_id'";
  $where["usermessage_dest.archived"] = " = '1'";
  $where['usermessage_dest.deleted'] = " = '0'";
  $order = "usermessage_dest.starred DESC, usermessage_dest.datetime_sent DESC";
  $total_found = CUserMessageDest::countArchivedFor($user);
}

if ($mode == "sentbox") {
  $where = array();
  $where["from_user_id"] = " = '$user->_id'";
  $where["datetime_sent"] = " IS NOT NULL";
  $total_found = CUserMessageDest::countSentFor($user);
}

if ($mode == "draft") {
  $order = "usermessage.usermessage_id DESC";
  $where = array();
  $where["creator_id"] = " = '$user->_id'";
  $where["datetime_sent"] = " IS NULL";
  $total_found = CUserMessageDest::countDraftedFor($user);
}

$total_found = $total_found ? $total_found : 0;

$group = "usermessage.usermessage_id";
/** @var CUserMessage[] $usermessages */
$usermessages = $usermessage->loadList($where, $order, "$page, $step", $group, $lj);

foreach ($usermessages as $_usermessage) {
  $_usermessage->loadRefDestUser();
  $_usermessage->loadRefsAttachments();
  /** @var CUserMessageDest[] $destinataires */
  $destinataires = $_usermessage->loadRefDests();

  /* We set the _ref_dest_user when the mode is draft for being able to delete a message */
  if ($mode == 'draft') {
    $_usermessage->_ref_dest_user = reset($_usermessage->_ref_destinataires);
  }

  foreach ($destinataires as $key => $_dest) {
    if (in_array($mode, array("inbox", "archive"))) {
      $_usermessage->_mode = "in";
      if ($_dest->to_user_id != $user->_id) {
        unset($destinataires[$key]);
        continue;
      }
    }

    if (in_array($mode, array("sentbox", "draft"))) {
      $_usermessage->_mode = "out";
      if ($_dest->from_user_id != $user->_id) {
        unset($destinataires[$key]);
        continue;
      }
    }

    $_dest->loadRefFrom()->loadRefFunction();
    $_dest->loadRefTo()->loadRefFunction();
  }
}

// smarty
$smarty = new CSmartyDP();
$smarty->assign("usermessages"  , $usermessages);
$smarty->assign("unread"        , $unread);
$smarty->assign("total"         , $total_found);
$smarty->assign("page"          , $page);
$smarty->assign("mode"          , $mode);
$smarty->assign('inputMode', CAppUI::pref('inputMode'));
$smarty->display("inc_list_usermessages.tpl");
