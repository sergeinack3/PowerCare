<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Ox\Mediboard\Patients\Constants\CConstantReleve;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

CCanDo::checkAdmin();
$cat   = CView::get("_category", "enum list|physio|biolo|activity|all");
$scope = CView::get("_scope"  , "num default|0");
CView::checkin();

$releve           = new CConstantReleve();
$releve->datetime = CMbDT::dateTime();
$constants_spec   = CConstantSpec::getListSpecByCategory($cat, $scope);
/** @var CConstantSpec $_constant_spec */
foreach ($constants_spec as $_constant_spec) {
  /** @var CAbstractConstant $value */
  $value                       = new $_constant_spec->value_class;
  $value->updateFormFields();
  $_constant_spec->_input_type = $value->_input_field;
}
$smarty = new CSmartyDP();
$smarty->assign("releve", $releve);
$smarty->assign("constantes", $constants_spec);
$smarty->display("inc_modal_create_releve.tpl");
