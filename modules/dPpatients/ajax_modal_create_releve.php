<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

CCanDo::checkAdmin();
$patient_id = CView::get("patient_id", "ref class|CPatient");
CView::checkin();

if (!$patient_id) {
  CAppUI::stepAjax(CAppUI::tr("CConstantReleve-msg-Patient not found"), UI_MSG_ERROR);
}

$smarty = new CSmartyDP();
$smarty->assign("patient_id", $patient_id);
$smarty->assign("constant_value", new CAbstractConstant());
$smarty->assign("user_id", CUser::get()->_id);
$smarty->assign("spec", new CConstantSpec());
$smarty->display("modal_create_releve.tpl");
