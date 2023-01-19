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
use Ox\Core\Handlers\Facades\HandlerManager;
use Ox\Mediboard\System\CObjectPseudonymiser;

CCanDo::checkAdmin();

$classes_handled = implode('|', array_keys(CObjectPseudonymiser::$classes_handled));

$continue       = CView::post('continue', 'bool default|0');
$class_selected = CView::post('class_selected', "enum list|$classes_handled notNull");
$last_id        = CView::post('last_id', 'num');
$count          = CView::post('count', 'enum list|' . implode('|', CObjectPseudonymiser::$counts) . ' default|100');
$delais         = CView::post('delais', 'num');
$pseudo_admin   = CView::post('pseudo_admin', 'bool default|1');

CView::checkin();

if (!$class_selected) {
  CAppUI::commonError('CObjectPseudonymiser-error-no class');
}

if (CAppUI::conf("instance_role") == 'prod') {
  CAppUI::stepAjax("system-Error-Pseudonymise cannot be done on prod", UI_MSG_ERROR);
}

HandlerManager::disableObjectHandlers();

$object_pseudonymiser = new CObjectPseudonymiser($class_selected);
$last_id              = $object_pseudonymiser->pseudonymiseSome($count, $last_id, $pseudo_admin);
$count_pseudo         = $object_pseudonymiser->getCount();

// Incrémente le "start"
CAppUI::js("ObjectPseudonymiser.setNextStart($last_id)");

$over = (($last_id === null) || ($count_pseudo < $count));

if ($over) {
  CAppUI::setMsg("CObjectPseudonymiser-msg-Over", UI_MSG_OK, CAppUI::tr("$class_selected|pl"));
  CAppUI::js("ObjectPseudonymiser.stopPseudonymise()");
}

echo CAppUI::getMsg();

if ($continue && !$over) {
  if ($delais) {
    $delais = $delais * 1000;
    CAppUI::js("setTimeout(ObjectPseudonymiser.nextPseudonymise, $delais)");
  }
  else {
    CAppUI::js("ObjectPseudonymiser.nextPseudonymise()");
  }
}

CApp::rip();
