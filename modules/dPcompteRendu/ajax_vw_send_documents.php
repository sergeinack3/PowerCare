<?php 
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Messagerie\CMailVariable;

CCanDo::checkRead();

$object_class = CView::get('object_class', 'str');
$object_id    = CView::get('object_id', 'ref meta|object_class');

CView::checkin();

$object = CMbObject::loadFromGuid("$object_class-$object_id");

if (!$object->_id) {
  trigger_error("object of class '$object_class' could not be loaded with id '$object_id'", E_USER_WARNING);
  CApp::rip();
}

$object->canDo();

$appFineClient_active = CModule::getActive("appFineClient");

if ($object->loadRefsDocs()) {
  foreach ($object->_ref_documents as $_doc) {
    $_doc->countSynchronizedRecipients();
    $_doc->isLocked();
    $_doc->canDo();
  }
}

$object->loadRefsFiles();
$object->loadRefsHyperTextLink();

if ($object->_ref_files) {
  foreach ($object->_ref_files as $_k => $_file) {
    $_file->countSynchronizedRecipients();
    $_file->canDo();

    if ($appFineClient_active) {
      CAppFineClient::loadBackRefOrderItem($_file);
    }
  }
}

if ($appFineClient_active) {
  CAppFineClient::loadIdex($object);
}

$mailVariable = new CMailVariable();
$subject = $mailVariable->replaceValue(CAppUI::pref('send_document_subject'), $object);
$body = $mailVariable->replaceValue(CAppUI::pref('send_document_body'), $object);

$smarty = new CSmartyDP();
$smarty->assign('send_document_subject' , $subject);
$smarty->assign('send_document_body'    , $body);
$smarty->assign('object'                , $object);
$smarty->assign("destinataires"         , CDocumentItem::getDestinatairesCourrier($object));
$smarty->assign('send_multiple_documents', true);

$smarty->display('inc_view_mail');
