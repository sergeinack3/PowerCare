<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantAlert;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

$constant_spec_id = CView::get("constant_spec_id", "num");
CView::checkin();

/** @var CConstantSpec $constant_spec */
$constant_spec = CConstantSpec::getSpecById($constant_spec_id);
$alert_num = array(1, 2, 3);

if (!$constant_spec->_ref_alert) {
   $constant_spec->_ref_alert = new CConstantAlert();
}
$alert     = $constant_spec->_ref_alert;

$by_category = array(
  "biolo"    => CConstantSpec::getListSpecByCategory("biolo", CConstantSpec::$TABLE_SPECS),
  "activity" => CConstantSpec::getListSpecByCategory("activity", CConstantSpec::$TABLE_SPECS),
  "physio"   => CConstantSpec::getListSpecByCategory("physio", CConstantSpec::$TABLE_SPECS)
);

$smarty = new CSmartyDP();
$smarty->assign("value_class", CConstantSpec::getConstantClasses());
$smarty->assign("spec", $constant_spec);
$smarty->assign("by_category", $by_category);
$smarty->assign("alert", $alert);
$smarty->assign("alert_num", $alert_num);
$smarty->display("modal_add_constant_spec");
