<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\BloodSalvage\CBloodSalvage;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
/* Récupération des variables en session et ou issues des formulaires.*/
$salle = CValue::getOrSession("salle");
$op    = CValue::getOrSession("op");
$date  = CValue::getOrSession("date", CMbDT::date());

$blood_salvage = new CBloodSalvage();

$selOp = new COperation();

if ($op) {
    $selOp->load($op);

    CAccessMedicalData::logAccess($selOp);

    $selOp->loadRefs();
    $where                 = [];
    $where["operation_id"] = "='$selOp->_id'";
    $blood_salvage->loadObject($where);
}

$smarty = new CSmartyDP();

$smarty->assign("blood_salvage", $blood_salvage);
$smarty->assign("blood_salvage_id", $blood_salvage->_id);
$smarty->assign("selOp", $selOp);
$smarty->assign("date", $date);

$smarty->display("vw_bloodSalvage.tpl");
