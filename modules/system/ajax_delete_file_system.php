<?php 
/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Mediboard\System\CSourceFileSystem;

CCanDo::checkAdmin();

$path   = CView::get("path", "str");
$source_guid = CView::get("source_guid", "str");
CView::checkin();

/** @var CSourceFileSystem $source */
$source = CMbObject::loadFromGuid($source_guid);

if (!$source->_id && !$path) {
  CAppUI::setMsg("CSourceFileSystem.none", UI_MSG_ERROR);
  echo CAppUI::getMsg();
  return;
}

$source->getClient()->delFile($path);

CAppUI::setMsg("CFile-msg-delete");
echo CAppUI::getMsg();
