<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CClassMap;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CRefCheckTable;

CCanDo::checkAdmin();

$class      = CView::get('class', 'str');
$field      = CView::get('field', 'str');
$continue   = CView::get('continue', 'bool default|0');
$chunk_size = CView::get('chunk_size', 'enum list|' . implode('|', array_keys(CRefCheckTable::$_chunk_size)) . ' default|10000');
$delay      = CView::get('delay', 'num default|0');
$js         = CView::get('js', 'bool default|0'); // Used to avoir errors with cron

CView::checkin();

//$mutex = new CMbMutex('check-integrity');
//if (!$mutex->lock(90)) { // TODO change lock time depending on the time limit
//  CAppUI::stepAjax("Verrou présent (check-integrity)");
//  return;
//}

//CApp::setMemoryLimit("1024M");
//CApp::setTimeLimit('90'); // TODO time limit and memory must depend on chunk_size

$ref = CRefCheckTable::getRefToCheck();

if ($ref && $ref->_id) {
  $ref->checkRefs($chunk_size);
}
else {
  CAppUI::setMsg("CRefCheckTable-msg-All over", UI_MSG_OK);
  if ($js) {
    CAppUI::js("ReferencesCheck.stopIntegrityCheck();");
  }
}

//$mutex->release();

if ($js && $ref && $ref->_id) {
  $class_short = CClassMap::getSN($ref->class);
  CAppUI::js("ReferencesCheck.updateInfos('{$class_short}', '{$ref->_current_ref_check_field->field}')");
}

echo CAppUI::getMsg();

if ($continue && $js) {
  $delay = (int)$delay * 1000;
  CAppUI::js("setTimeout(ReferencesCheck.nextStep, $delay)");
}
