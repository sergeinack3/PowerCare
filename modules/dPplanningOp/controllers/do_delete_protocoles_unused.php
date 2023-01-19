<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSQLDataSource;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CProtocole;

CApp::setMemoryLimit('2048M');
CApp::setTimeLimit(300);

CCanDo::checkAdmin();

$protocoles_ids = CView::post("protocoles_ids", "str");

CView::checkin();

$protocoles_ids = explode("-", $protocoles_ids);

if (!count($protocoles_ids)) {
  return;
}

$protocole = new CProtocole();

$protocoles = $protocole->loadList(["protocole_id" => CSQLDataSource::prepareIn($protocoles_ids)]);

foreach ($protocoles as $_protocole) {
  $msg = $_protocole->delete();
  CAppUI::setMsg($msg ?: "CProtocole-msg-delete", $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();
