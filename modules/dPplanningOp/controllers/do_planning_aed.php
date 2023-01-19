<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CDoObjectAddEdit;
use Ox\Core\Module\CModule;
use Ox\Core\CValue;
use Ox\Mediboard\System\Forms\CExClassEvent;

global $m;

$do = new CDoObjectAddEdit("COperation");
$do->doBind();

if (intval(CValue::post("del", null))) {
  CValue::setSession("operation_id");
  $do->redirectDelete = "m=$m&tab=vw_edit_planning&operation_id=0";
  $do->doDelete();
}
else {
  if ($do->_obj->plageop_id && ($do->_old->plageop_id != $do->_obj->plageop_id)) {
    $do->_obj->rank = 0;
  }

  $do->doStore();

  if (CModule::getActive("forms")) {
      if (CValue::post("_set_fin_op") && CValue::post("fin_op") == "current") {
          $ex_class_events = CExClassEvent::getForObject($do->_obj, "fin_intervention", "required");
          echo CExClassEvent::getJStrigger($ex_class_events);
      }
      elseif (CValue::post("sortie_sans_sspi")) {
          $ex_class_events = CExClassEvent::getForObject($do->_obj, "sortie_sans_sspi_auto", "required");
          CAppUI::js("ExObject.onAfterSave = function() { SalleOp.reloadTimingTab(); };");
          echo CExClassEvent::getJStrigger($ex_class_events);
      }

  }

  $m = CValue::post("otherm", $m);
  if ($m == "dPhospi") {
    $do->redirectStore = "m=$m#operation".$do->_obj->operation_id;
  }
  $do->redirectStore = "m=$m&operation_id=".$do->_obj->operation_id;
}

$do->doRedirect();
