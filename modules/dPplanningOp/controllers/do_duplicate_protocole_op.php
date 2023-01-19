<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CProtocoleOperatoire;

CCanDo::checkEdit();

$protocole_operatoire_id = CView::post("protocole_operatoire_id", "ref class|CProtocoleOperatoire");

CView::checkin();

$protocole_op = new CProtocoleOperatoire();
$protocole_op->load($protocole_operatoire_id);

$materiels = $protocole_op->loadRefsMaterielsOperatoires();

$protocole_op->_id = "";
$protocole_op->libelle = CAppUI::tr("CProtocoleOperatoire-Copy of", $protocole_op->libelle);
$msg = $protocole_op->store();

CAppUI::setMsg($msg ? : "CProtocoleOperatoire-msg-create", $msg ? UI_MSG_ERROR : UI_MSG_OK);

if ($msg) {
  return;
}

foreach ($materiels as $_materiel) {
  $_materiel->_id = "";
  $_materiel->protocole_operatoire_id = $protocole_op->_id;
  $_materiel->store();
}

CAppUI::callbackAjax("Form.onSubmitComplete", $protocole_op->_guid);
