<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CUserMail;

CCanDo::checkRead();

$mail_ids = json_decode(stripslashes(CView::get('mail_ids', 'str default|[]')));

CView::checkin();

$user  = CMediusers::get();
$mails = [];

foreach ($mail_ids as $mail_id) {
    $mail = new CUserMail();
    $mail->load($mail_id);
    $mail->loadRefsFwd();
    $mails[] = $mail;
}

$smarty = new CSmartyDP();
$smarty->assign("mails", $mails);
$smarty->display("print_mails.tpl");
