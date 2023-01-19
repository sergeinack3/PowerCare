<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// array
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CDocumentExterne;

CCanDo::check();

$type = CView::post("type", 'str');
$document_string = CView::post("document", 'str');

CView::checkin();

$documents = explode(",", $document_string);
CMbArray::removeValue("", $documents);

if (!count($documents)) {
  CAppUI::stepAjax("CBioserveurDocument-msg-pls_select_at_least_one_doc", UI_MSG_WARNING);
  return;
}

$archived = 0;
$delete = 0;

foreach ($documents as $_doc) {
  /** @var CDocumentExterne $object */
  $object = CMbObject::loadFromGuid($_doc);

  switch ($type) {

    case 'star':
      $object->starred = 1;
      if ($msg = $object->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        $archived ++;
      }
      break;

    case 'archive':
      $object->archived = 1;
      if ($msg = $object->store()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        $archived ++;
      }
      break;

    case 'delete':
      if ($msg = $object->purge()) {
        CAppUI::setMsg($msg, UI_MSG_WARNING);
      }
      else {
        $delete++;
      }
      break;

    default:
      CAppUI::setMsg("nothing_to_do");
      return;
  }
}

echo CAppUI::getMsg();