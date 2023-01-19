<?php
/**
 * @package Mediboard\Mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Patients\CMedecin;

CCanDo::checkRead();

$user_id    = CView::post('user_id', 'ref class|CMediusers notNull');
$medecin_id = CView::post('medecin_id', 'ref class|CMedecin notNull');
$link       = CView::post('link', 'bool default|1');

CView::checkin();

$medecin = new CMedecin();
$medecin->load($medecin_id);
if (!$medecin || !$medecin->_id) {
  CAppUI::stepAjax('CMedecin.none', UI_MSG_ERROR);
}

$medecin->user_id = ($link) ? $user_id : '';
if ($msg = $medecin->store()) {
  CAppUI::setMsg($msg, UI_MSG_WARNING);
}
else {
  CAppUI::setMsg('CMedecin-msg-modify', UI_MSG_OK);
}

echo CAppUI::getMsg();