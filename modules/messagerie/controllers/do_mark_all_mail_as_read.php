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
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CPop;
use Ox\Mediboard\Messagerie\CUserMail;
use Ox\Mediboard\System\CSourcePOP;

CCanDo::checkRead();
CPop::checkImapLib();

$account_id = CView::get("account_id", 'ref class|CSourcePOP');

CView::checkin();

$user = CMediusers::get();

//get account
$account_pop = new CSourcePOP();
$account_pop->load($account_id);

//get the list
$mail = new CUserMail();
$where = array();
$where[] = "date_read IS NULL AND account_id = '$account_id' AND account_class = 'CSourcePOP'";
$mails = $mail->loadList($where);

$pop = new CPop($account_pop);
$pop->open();
$count = 0;
/** @var CUserMail[] $mails */
foreach ($mails as $_mail) {
  if ($pop->setFlag($_mail->uid, "\\Seen")) {
    $_mail->date_read = CMbDT::dateTime();
    if (!$msg = $_mail->store() ) {
      $count++;
    }
  }
}
$pop->close();

if ($count > 0) {
  CAppUI::stepAjax("CUserMail-markedAsRead", UI_MSG_OK, $count);
}
else {
  CAppUI::stepAjax("CUserMail-markedAsRead-none", UI_MSG_OK);
}