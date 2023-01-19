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
use Ox\Mediboard\System\CTranslationOverwrite;

CCanDo::checkAdmin();

$start = CView::post('start', 'num default|0');
$step  = CView::post('step', 'num default|50');

CView::checkin();

$locales = CAppUI::getLocalesFromFiles();

$trad  = new CTranslationOverwrite();
$trads = $trad->loadList(null, null, "$start,$step");

$trads_ids = array();
/** @var CTranslationOverwrite $_trad */
foreach ($trads as $_trad) {
  $_trad->loadOldTranslation($locales);

  if ($_trad->_old_translation == $_trad->translation) {
    $trads_ids[] = $_trad->_id;
  }
}

if ($trads_ids) {
  if ($msg = $trad->deleteAll($trads_ids)) {
    CAppUI::setMsg($msg, UI_MSG_ERROR);
  }
  else {
    CAppUI::setMsg('CTraductionOverwrite-msg-delete-%d', UI_MSG_OK, count($trads_ids));
  }
}
else {
  CAppUI::setMsg('CTraductionOverwrite-msg-nothing to purge', UI_MSG_OK);
}

echo CAppUI::getMsg();

CApp::rip();