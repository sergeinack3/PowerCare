<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

$value_class = CView::get("value_class", "str");
CView::checkin();
$smarty = new CSmartyDP();
$smarty->assign("value_class", $value_class);
$smarty->assign("spec"       , new CConstantSpec());
$smarty->display("inc_constant_spec_type_value.tpl");
