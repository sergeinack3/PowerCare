<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;

CCanDo::checkEdit();

$plageop_id = CView::post("plageop_id", "ref class|CPlageOp");

CView::checkin();

$plage = new CPlageOp();
$plage->load($plageop_id);

foreach ($plage->loadRefsOperations() as $_op) {
  $_op->plageop_id = "";
  $_op->date = $plage->date;
  $_op->salle_id = $plage->salle_id;

  $msg = $_op->store();

  CAppUI::setMsg($msg ? : CAppUI::tr("CPlageOp-msg-modify"), $msg ? UI_MSG_ERROR : UI_MSG_OK);
}

echo CAppUI::getMsg();