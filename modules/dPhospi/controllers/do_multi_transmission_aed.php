<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CDoObjectAddEdit;
use Ox\Core\CValue;

$del        = CValue::post("del");
$callback   = CValue::post("callback");
$data_id    = CValue::post("data_id");
$action_id  = CValue::post("action_id");
$result_id  = CValue::post("result_id");
$locked     = CValue::post("_locked");
$dietetique = CValue::post("dietetique");

$do                                = new CDoObjectAddEdit("CTransmissionMedicale", "data_id");
$_POST["transmission_medicale_id"] = isset($_POST["data_id"]) ? $_POST["data_id"] : "";
$do->doBind();

$cible_id = "";

if ($del && $data_id) {
  $do->doDelete();
}
else {
  if ($do->_obj->_text_data) {
    $do->_obj->text       = $do->_obj->_text_data;
    $do->_obj->type       = "data";
    $do->_obj->dietetique = $dietetique;

    $do->doStore();

    $cible_id                  = $do->_obj->cible_id;
    $_POST["_force_new_cible"] = "0";

    if (!$_POST["_text_action"] && !$_POST["_text_result"] && $locked) {
      $do->_obj->locked = 1;
      $do->_obj->store();
    }
  }
  elseif ($do->_obj->_id) {
    $do->doStore();
  }
}

$do                                = new CDoObjectAddEdit("CTransmissionMedicale", "action_id");
$_POST["transmission_medicale_id"] = isset($_POST["action_id"]) ? $_POST["action_id"] : "";
$do->doBind();
if ($del && $action_id) {
  $do->doDelete();
}
else {
  if ($do->_obj->_text_action) {
    $do->_obj->text       = $do->_obj->_text_action;
    $do->_obj->type       = "action";
    $do->_obj->dietetique = $dietetique;
    if ($cible_id) {
      $do->_obj->cible_id = $cible_id;
    }

    $do->doStore();

    $cible_id                  = $do->_obj->cible_id;
    $_POST["_force_new_cible"] = "0";

    if (!$_POST["_text_result"] && $locked) {
      $do->_obj->locked = 1;
      $do->_obj->store();
    }
  }
  elseif ($do->_obj->_id) {
    $do->doStore();
  }
}

$do                                = new CDoObjectAddEdit("CTransmissionMedicale", 'result_id');
$_POST["transmission_medicale_id"] = isset($_POST["result_id"]) ? $_POST["result_id"] : "";
$do->doBind();
if ($del && $result_id) {
  $do->doDelete();
}
else {
  if ($do->_obj->_text_result) {
    $do->_obj->text       = $do->_obj->_text_result;
    $do->_obj->type       = "result";
    $do->_obj->dietetique = $dietetique;
    if ($cible_id) {
      $do->_obj->cible_id = $cible_id;
    }

    $do->doStore();

    if ($locked) {
      $do->_obj->locked = 1;
      $do->_obj->store();
    }
  }
  elseif ($do->_obj->_id) {
    $do->doStore();
  }
}

$do->callBack = $callback;
$do->ajax     = 1;
$do->doRedirect();
