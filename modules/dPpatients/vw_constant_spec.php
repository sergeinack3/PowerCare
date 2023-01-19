<?php
/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Patients\Constants\CConstantAlert;
use Ox\Mediboard\Patients\Constants\CConstantSpec;

$refresh = CView::get("refresh", "num default|0");
$reset_cache = CView::get("reset_cache", "num default|0");
CView::checkin();
$refresh ? $tpl = "inc_table_constant_spec.tpl" : $tpl = "vw_constant_spec.tpl";
if ($reset_cache) {
  CConstantSpec::resetListConstants();
}

$spec = new CConstantSpec();
$xml  = CConstantSpec::getListSpecById(CConstantSpec::$XML_SPECS);
$base = CConstantSpec::getListSpecById(CConstantSpec::$TABLE_SPECS);
CMbArray::ksortByProp($xml, "constant_spec_id");
CMbArray::ksortByProp($base, "constant_spec_id");
$constants_spec = array(
  "0" => $xml,
  "1" => $base
);

$height = CConstantSpec::getSpecByCode("height");
$height->deserializeUnit();

$smarty = new CSmartyDP();
$smarty->assign("constants_spec", $constants_spec);
$smarty->assign("spec", $spec);
$smarty->assign("alert", new CConstantAlert());
$smarty->display("$tpl");

