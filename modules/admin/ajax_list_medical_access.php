<?php
/**
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CLogAccessMedicalData;

CCanDo::checkAdmin();

$object = CMbObject::loadFromGuid(CValue::get("guid"));
$page = CValue::get("page", 0);
$step = 20;

$total = CLogAccessMedicalData::countListForSejour($object);
$list = CLogAccessMedicalData::loadListForSejour($object, $page, $step);
foreach ($list as $_list) {
  $_list->loadRefUser()->loadRefFunction();
}

$smarty = new CSmartyDP();
$smarty->assign("list", $list);
$smarty->assign("page", $page);
$smarty->assign("step", $step);
$smarty->assign("total", $total);
$smarty->display("inc_list_medical_access.tpl");