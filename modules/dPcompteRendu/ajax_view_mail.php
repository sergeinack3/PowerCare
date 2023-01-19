<?php
/**
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\CompteRendu\CCompteRendu;
use Ox\Mediboard\Files\CDocumentItem;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Messagerie\CMailVariable;
use Ox\Mediboard\System\CExchangeSource;
use Ox\Mediboard\System\CSourceSMTP;

/**
 * Modale des destinataires possibles pour un docitem
 */
CCanDo::checkRead();

$object_guid = CView::get("object_guid", 'str');
CView::checkin();

$object = CMbObject::loadFromGuid($object_guid);

if (!$object || !$object->_id) {
  CAppUI::stepAjax('CCompteRendu-alert_doc_not_saved', UI_MSG_ERROR);
}

$user = CMediusers::get();
/** @var CSourceSMTP $source */
$source = CExchangeSource::get("mediuser-" . $user->_id, CSourceSMTP::TYPE);
if (!$source->_id) {
  $source->address_type = 'mail';
}

$target_object = $object->loadTargetObject();

if (get_class($target_object) === CCompteRendu::class) {
  $target_object = $target_object->loadTargetObject();
}
$mailVariable = new CMailVariable();
$subject = $mailVariable->replaceValue(CAppUI::pref('send_document_subject'), $object);
$body = $mailVariable->replaceValue(CAppUI::pref('send_document_body'), $object);

$smarty = new CSmartyDP();

$smarty->assign('send_document_subject', $subject);
$smarty->assign('send_document_body', $body);
$smarty->assign("object"       , $object);
$smarty->assign("destinataires", CDocumentItem::getDestinatairesCourrier($target_object, $source->address_type));

$smarty->display("inc_view_mail");
