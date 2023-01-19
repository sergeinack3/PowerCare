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
use Ox\Core\CMbObject;
use Ox\Core\CView;

CCanDo::checkAdmin();

$back_ref_field = CView::post("back_ref_field", "str notNull");
$back_ref_id = CView::post("back_ref_id", "str notNull");
$back_ref_class = CView::post("back_ref_class", "str notNull");

CView::checkin();

/** @var CMbObject $object_back */
$object_back = new $back_ref_class();
if (!$object_back) {
  CAppUI::stepAjax("Impossible de retrouver la back ref.", UI_MSG_ERROR);
}

$object_back->load($back_ref_id);
if (!$object_back->_id) {
  CAppUI::stepAjax("Impossible de retrouver la back ref.", UI_MSG_ERROR);
}

$object_back->{$back_ref_field} = "";

if ($msg = $object_back->store()) {
  CAppUI::stepAjax($msg, UI_MSG_ERROR);
}

CAppUI::stepAjax("$back_ref_class-msg-modify", UI_MSG_OK);
CApp::rip();
