<?php

/**
 * @package Mediboard\BloodSalvage
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$date         = CValue::getOrSession("date", CMbDT::date());
$operation_id = CValue::getOrSession("operation_id");

CAccessMedicalData::logAccess("COperation-$operation_id");

// Liste des blocs
$listBlocs = new CBlocOperatoire();
$listBlocs = $listBlocs->loadGroupList();

// Chargement des chirurgiens
$listPermPrats = new CMediusers();
$listPermPrats = $listPermPrats->loadPraticiens(PERM_READ);
$listPrats     = [];
$plagesJour    = new CPlageOp();
$where         = [];
$where["date"] = "= '$date'";
$groupby       = "chir_id";
$plagesJour    = $plagesJour->loadList($where, null, null, $groupby);
foreach ($plagesJour as $curr_plage) {
    if (array_key_exists($curr_plage->chir_id, $listPermPrats)) {
        $listPrats[$curr_plage->chir_id] = $listPermPrats[$curr_plage->chir_id];
    }
}
$opsJour       = new COperation();
$where         = [];
$where["date"] = "= '$date'";
$groupby       = "chir_id";
$opsJour       = $opsJour->loadList($where, null, null, $groupby);
foreach ($opsJour as $curr_op) {
    if (array_key_exists($curr_op->chir_id, $listPermPrats)) {
        $listPrats[$curr_op->chir_id] = $listPermPrats[$curr_op->chir_id];
    }
}
$listPrats = CMbArray::pluck($listPrats, "_view");
asort($listPrats);

// Selection des plages opératoires de la journée
$praticien = new CMediusers();
if ($praticien->load(CValue::getOrSession("praticien_id"))) {
    $praticien->loadRefsForDay($date);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("vueReduite", false);
$smarty->assign("praticien", $praticien);
$smarty->assign("salle", new CSalle());
$smarty->assign("listBlocs", $listBlocs);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("date", $date);
$smarty->assign("operation_id", $operation_id);

$smarty->display("inc_liste_op_prat.tpl");
