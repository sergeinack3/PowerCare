<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

CCanDo::checkAdmin();
$constant_code = CView::get("constant_name", "str");
CView::checkin();

$spec           = CConstantSpec::getSpecByCode($constant_code);
$spec->alert_id = null;
try {
  if ($msg = $spec->_ref_alert->delete()) {
    throw new CConstantException(CConstantException::INVALID_DELETE_ALERT, $msg);
  }

  if ($spec->_is_constant_base) {
    if ($msg = $spec->store()) {
      throw new CConstantException(CConstantException::INVALID_STORE_SPEC, $msg);
    }
  }
  else {
    CConstantSpec::resetListConstants();
  }
} catch (CConstantException $constantException) {
  CAppUI::displayAjaxMsg($constantException->getMessage(), UI_MSG_ERROR);
}
