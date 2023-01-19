<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\System\CMessage;
use Ox\Mediboard\System\CMessageAcquittement;

CCanDo::checkRead();

$page       = CView::get("page", "num");
$limit      = CView::get("limit", "num");
$step       = CView::get("step", "num");
$message_id = CView::get("message_id", "ref class|CMessage");

CView::checkin();

$acquittal  = new CMessageAcquittement();
$total      = 0;
$acquittals = [];

if ($message_id) {
  $message    = CMessage::findOrFail($message_id);
  $acquittals = $message->loadBackRefs("acquittals", "date desc", "$limit, " . ($limit + $step));
  $total      = $message->countBackRefs("acquittals");

  $users = CStoredObject::massLoadFwdRef($acquittals, "user_id");
  CStoredObject::massLoadFwdRef($users, "function_id");
  foreach ($acquittals as $_acquittal) {
    $_acquittal->loadRefUser()->loadRefFunction();
  }
}

$smarty = new CSmartyDP();
$smarty->assign("acquittals", $acquittals);
$smarty->assign("limit", $limit);
$smarty->assign("page", $page);
$smarty->assign("total", $total);
$smarty->display("inc_acquitment_list");