<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\CompteRendu\CTemplateManager;
use Ox\Mediboard\Messagerie\CUserMessage;

CCanDo::checkRead();
$user = CUser::get();
$usermessage = new CUserMessage();
$usermessage->from        = $user->_id;
$usermessage->to          = CView::get("to", 'ref class|CMediusers');
$usermessage->subject     = CView::get("subject", 'str');
$usermessage->in_reply_to = CView::get("in_reply_to", 'ref class|CUserMessage');
$usermessage->load(CView::get("usermessage_id", 'ref class|CUserMessage', true));
$usermessage->loadRefsFwd();

CView::checkin();

// Vérifiction de la première lecture par le destinataire
if ($usermessage->to == $user->_id && $usermessage->date_sent && ! $usermessage->date_read) {
  $usermessage->date_read = CMbDT::dateTime();
  $usermessage->store();
}

if ($usermessage->in_reply_to) {
  $origin = $usermessage->loadOriginMessage();
  if ($origin->_id) {
    if (!$usermessage->subject) {
      $usermessage->subject = "Re: ".$origin->subject;
    }
    $usermessage->to = $origin->from;
  }
}

if ($usermessage->to) {
  $usermessage->loadRefUsersTo();
}
// Historique des messages avec le destinataire
$where = array();
$where[] = "(usermessage.from = '$usermessage->from' AND usermessage.to = '$usermessage->to')".
           "OR (usermessage.from = '$usermessage->to' AND usermessage.to = '$usermessage->from')";
$where["date_sent"] =" IS NOT NULL";

$historique = $usermessage->loadList($where, "date_sent DESC", "20", "date_sent, subject");
CMbObject::massLoadFwdRef($historique, "from");
CMbObject::massLoadFwdRef($historique, "to");

/** @var CUserMessage[] $historique */
foreach ($historique as $_mail) {
  $_mail->loadRefUserFrom();
  $_mail->loadRefUsersTo();
}

// Initialisation de CKEditor
$templateManager = new CTemplateManager();
$templateManager->editor = "ckeditor";
$templateManager->simplifyMode = true;

if ($usermessage->date_sent) {
  $templateManager->printMode = true;
}

$templateManager->initHTMLArea();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("usermessage"   , $usermessage);
$smarty->assign("historique", $historique);

$smarty->display("write_usermessage.tpl");
