<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CMailPartToFile;

/**
 * Unlink an attachment
 */
CCanDo::checkEdit();

$link_id = CView::get('link_id', 'ref class|CMailPartToFile');

CView::checkin();

$link = new CMailPartToFile();
$link->load($link_id);
$file = $link->loadRefFile();
$part = $link->loadRefPart();

if (($part->_class == 'CMailAttachments' && $part->file_id == $file->_id)
    || ($part->_class == 'CUserMail' && $part->text_file_id == $file->_id)
) {
  $file->object_class = $part->_class;
  $file->object_id = $part->_id;
  if ($msg = $file->store()) {
    CAppUI::stepAjax("CMailAttachments-unlinked-failed", UI_MSG_ERROR, $msg);
    CApp::rip();
  }

  if ($msg = $link->delete()) {
    CAppUI::stepAjax("CMailAttachments-unlinked-failed", UI_MSG_ERROR, $msg);
    CApp::rip();
  }
}
else {
  if ($msg = $link->delete()) {
    CAppUI::stepAjax("CMailAttachments-unlinked-failed", UI_MSG_ERROR, $msg);
    CApp::rip();
  }

  if ($msg = $link->_ref_file->delete()) {
    CAppUI::stepAjax("CMailAttachments-unlinked-failed", UI_MSG_ERROR, $msg);
    CApp::rip();
  }
}

CAppUI::stepAjax("CMailAttachments-unlinked", UI_MSG_OK);
