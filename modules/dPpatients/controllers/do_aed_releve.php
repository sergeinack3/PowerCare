<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

CCanDo::checkAdmin();
$releve_id  = CView::get("releve_id", "ref class|CConstantReleve");
$datetime   = CView::get("datetime", "dateTime");
$patient_id = CView::get("patient_id", "ref class|CPatient");
$user_id    = CView::get("user_id", "ref class|CUser");
$source     = CView::get("source", "enum list|self|manuel|device|api");

if (!$source) {
  $source = CConstantReleve::FROM_MEDIBOARD;
}

$constant_params = array();
foreach ($_GET as $_key => $_value) {
  if (!$_value) {
    continue;
  }

  $constant_id = CMbArray::get(explode("constant_", $_key), 1, null);
  if (!$constant_id || strstr($constant_id, "_da") || strstr($constant_id, "max_")) {
    continue;
  }

  if (strstr($constant_id, "min_")) {
    $constant_id = CMbArray::get(explode("_", $constant_id), 1);
  }

  $spec = CConstantSpec::getSpecById($constant_id);

  $data              = array();
  $data["spec_code"] = $spec->code;
  $data["datetime"]  = $datetime;
  $data["period"]    = $spec->period;
  $data["source"]    = $source;
  $data["validated"] = 1;
  $data["value"]     = array();
  if ($value = CMbArray::get($_GET, "constant_min_" . $spec->_id)) {
    $data["min_value"] = $value;
  }

  if ($value = CMbArray::get($_GET, "constant_max_" . $spec->_id)) {
    $data["max_value"] = $value;
  }

  if ($value = CMbArray::get($_GET, "constant_" . $spec->_id)) {
    $data["value"] = $value;
  }

  if ($releve_id) {
    $data["releve_id"] = $releve_id;
  }

  $constant_params[] = $data;
}
CView::checkin();

try {
  $result = CConstantReleve::storeReleveAndConstants($constant_params, $patient_id, $user_id);

  if (count($exceptions = CMbArray::get($result, "exceptions")) !== 0) {
    foreach ($exceptions as $_exception_code => $_counter) {
      $type = UI_MSG_WARNING;
      if ($_exception_code === CConstantException::IDENTICAL_CONSTANT) {
        $type = UI_MSG_ALERT;
      }
      CAppUI::displayAjaxMsg(
        CAppUI::tr("CConstantException-msg-error", $_counter, CAppUI::tr("CConstantException-$_exception_code")), $type
      );
    }
  }

  if (($saved = CMbArray::getRecursive($result, "report constant_saved")) !== 0) {
    CAppUI::displayAjaxMsg(CAppUI::tr("CAbstractConstant-msg-add (%s)", $saved), UI_MSG_OK);
  }
}
catch (NXP\Exception\UnknownFunctionException $e) {
  CAppUI::displayAjaxMsg("CConstantSpec-msg-error this function doesn t exist", UI_MSG_ERROR);
}
catch (NXP\Exception\UnknownTokenException $ute) {
  CAppUI::displayAjaxMsg("CConstantSpec-msg-error this token doesn t exist", UI_MSG_ERROR);
}
catch (NXP\Exception\UnknownOperatorException $uoe) {
  CAppUI::displayAjaxMsg("CConstantSpec-msg-error this operator doesn t exist", UI_MSG_ERROR);
}
catch (NXP\Exception\UnknownVariableException $uve) {
  CAppUI::displayAjaxMsg("CConstantSpec-msg-error this variable doesn t known", UI_MSG_ERROR);
}
catch (NXP\Exception\IncorrectExpressionException $iee) {
  CAppUI::displayAjaxMsg("CConstantSpec-msg-error in expression on formula", UI_MSG_ERROR);
}
catch (CConstantException $e) {
}
