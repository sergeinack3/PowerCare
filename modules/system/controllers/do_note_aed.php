<?php
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CPermObject;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\System\CNote;

CCanDo::check();

$share_ids = CView::post('_share_ids', 'str');

CView::checkin();

$note = new CNote();
$note->bind($_POST);

$note->needsEdit();

if ($msg = $note->store()) {
  CAppUI::setMsg($msg, UI_MSG_ERROR);
  CApp::rip();
}

if (!$note->public && $share_ids) {
  $ids = explode('|', $share_ids);

  foreach ($ids as $_id) {
    $_user = new CMediusers();
    $_user->load($_id);

    if (!$_user->canDo()->read) {
      continue;
    }

    $_perm               = new CPermObject();
    $_perm->object_class = $note->_class;
    $_perm->object_id    = $note->_id;
    $_perm->user_id      = $_user->_id;
    $_perm->permission   = PERM_READ;

    if ($_msg = $_perm->store()) {
      CAppUI::setMsg($_msg, UI_MSG_WARNING);
    }
  }
}

echo CAppUI::getMsg();
CApp::rip();