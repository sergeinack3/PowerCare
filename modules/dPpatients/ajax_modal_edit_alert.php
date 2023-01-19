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

$name = CView::get("name", "str");
CView::checkin();
$spec = CConstantSpec::getSpecByCode($name);
$spec->_alert ? $alert = $spec->_alert : $alert = new CConstantAlert();

$smarty = new CSmartyDP();
$smarty->assign("alert", $alert);
$smarty->assign("spec", $spec);
$smarty->assign("alert_num", array(1, 2, 3));
$smarty->display("inc_edit_alert");
