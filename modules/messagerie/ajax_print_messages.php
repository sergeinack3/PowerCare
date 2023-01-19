<?php

/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbString;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CUserMessageDest;

CCanDo::checkRead();

$messages_dest_ids = json_decode(stripslashes(CView::get('messages_dest_ids', 'str default|[]')));

CView::checkin();

$messages = [];

foreach ($messages_dest_ids as $message_dest_id) {
    $message_dest = new CUserMessageDest();
    $message_dest->load($message_dest_id);
    $message = $message_dest->loadRefMessage();

    $message->sanitizeContent();

    $message->loadRefDestUser();
    $message->loadRefCreator();
    $message->loadRefDests();
    $messages[] = $message;
    foreach ($message->_ref_destinataires as $_dest) {
        $_dest->loadRefTo();
    }
}

$smarty = new CSmartyDP();
$smarty->assign("messages", $messages);
$smarty->display("print_messages.tpl");
