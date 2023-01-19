<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\System\CNote;

CCanDo::check();

$note_id     = CView::get('note_id', 'ref class|CNote');
$object_guid = CView::get('object_guid', 'str');

CView::checkin();

$note = new CNote();
$note->load($note_id);

if (!$note->_id) {
  $note->setObject(CStoredObject::loadFromGuid($object_guid));
  $note->user_id = CUser::get()->_id;
  $note->date    = CMbDT::dateTime();
}

$note->loadTargetObject()->needsRead();
$note->loadRefUser()->loadRefFunction();
$note->needsEdit();

$smarty = new CSmartyDP();
$smarty->assign('note', $note);
$smarty->display('edit_note.tpl');
