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

CCanDo::checkEdit();

$trads = CView::post('translations', 'str');

CView::checkin();

if ($trads) {
  $trads = json_decode(utf8_encode(stripcslashes($trads)));
}

if (!$trads) {
  CAppUI::commonError('CTranslationOverwrite.none');
}

$keys_ok = array();
foreach ($trads as $_trad) {
  $overwrite_trad           = new CTranslationOverwrite();
  $overwrite_trad->language = $_trad->lang;
  $overwrite_trad->source   = utf8_decode($_trad->key);

  $overwrite_trad->loadMatchingObjectEsc();

  $overwrite_trad->translation = utf8_decode($_trad->trad);

  $new = ($overwrite_trad->_id) ? 'modify' : 'create';
  if ($msg = $overwrite_trad->store()) {
    CAppUI::setMsg($msg, UI_MSG_WARNING);
  }
  else {
    $keys_ok[] = $_trad->key;
    CAppUI::setMsg("CTranslationOverwrite-msg-$new", UI_MSG_OK);
  }
}

echo CAppUI::getMsg();

CApp::rip();