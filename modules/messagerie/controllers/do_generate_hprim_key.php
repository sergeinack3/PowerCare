<?php
/**
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbSecurity;
use Ox\Core\CView;
use Ox\Mediboard\Messagerie\CSMimeHandler;

CCanDo::checkAdmin();

CView::checkin();

$key = CMbSecurity::getRandomString(4096);

$key_path = CSMimeHandler::getMasterKeyPath();

if (file_put_contents($key_path, $key)) {
  CAppUI::setMsg('HPRIM.NET-msg-key_generated', UI_MSG_OK);
}
else {
  CAppUI::setMsg('HPRIM.NET-msg-error_while_generating_key', UI_MSG_ERROR);
}

echo CAppUI::getMsg();
