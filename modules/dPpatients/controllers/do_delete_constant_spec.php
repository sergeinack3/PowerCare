<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantException;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

$constant_id = CView::get("constant_id", "ref class|CConstantSpec");
CView::checkin();
$constant_spec = new CConstantSpec();
$constant_spec->load($constant_id);
try {

  if ($msg = $constant_spec->storeInactive()) {
    throw new CConstantException(CConstantException::INVALID_DELETE_SPEC, $msg);
  }
  CAppUI::displayAjaxMsg(CAppUI::tr("CConstantSpec-msg-delete"), UI_MSG_OK);
} catch (CConstantException $constantException) {
}
