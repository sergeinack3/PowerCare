<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();

if (null == $sip_barcode = CValue::get("sip_barcode")) {
  return;
}

$values = array();
if (!preg_match("/SID([\d]+)/i", $sip_barcode, $values)) {
  CAppUI::stepAjax("Le num�ro saisi '%s' ne correspond pas � un idenfitiant de s�jour", UI_MSG_WARNING, $sip_barcode);

  return;
}

$sejour = new CSejour;
$sejour->load($values[1]);
if (!$sejour->_id) {
  CAppUI::stepAjax("Le s�jour dont l'idenfitiant est '%s' n'existe pas", UI_MSG_WARNING, $sejour->_id);

  return;
}

$sejour->loadRefRPU();
if (!in_array($sejour->type, CSejour::getTypesSejoursUrgence($sejour->praticien_id)) && !$sejour->_ref_rpu->_id) {
  CAppUI::stepAjax("Le s�jour trouv� '%s' n'est pas un s�jour d'urgences", UI_MSG_WARNING, $sejour->_view);

  return;
}

CAppUI::redirect("m=urgences&tab=vw_idx_rpu&sejour_id=$sejour->_id");
