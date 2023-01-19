<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\PlanningOp\COperation;

$selOp         = new COperation();
$blood_salvage = new CBloodSalvage();
$date          = CValue::getOrSession("date", CMbDT::date());
$op            = CValue::getOrSession("op");

if ($op) {
    $selOp->load($op);

    CAccessMedicalData::logAccess($selOp);

    $selOp->loadRefs();
    $where                 = [];
    $where["operation_id"] = "='$selOp->_id'";
    $blood_salvage->loadObject($where);
    $blood_salvage->loadRefsFwd();
    $blood_salvage->loadRefPlageOp();
}

$smarty = new CSmartyDP();

$smarty->assign("date", $date);
$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("selOp", $selOp);

$smarty->display("vw_bloodSalvage_sspi.tpl");
