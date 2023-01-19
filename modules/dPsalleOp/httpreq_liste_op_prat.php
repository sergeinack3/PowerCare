<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$date           = CView::get("date", "date default|now", true);
$operation_id   = CView::get("operation_id", "ref class|COperation", true);
$hide_finished  = CView::get("hide_finished", "bool default|0");
$praticien_id   = CView::get("praticien_id", "num", true);
CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

// Chargement de l'utilisateur courant
$user = CMediusers::get();

if (!$praticien_id && $user->isPraticien() && !$user->isAnesth()) {
  $praticien_id = $user->user_id;
}

// Selection des salles
$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ);

// Chargement des chirurgiens ayant une intervention ce jour
$listPermPrats = $user->loadPraticiens(PERM_READ);
$listPrats  = array();
$operation = new COperation();
$operation->date = $date;
$operation->annulee = '0';
$groupby = "operations.chir_id";
/** @var COperation[] $operations */
$operations = $operation->loadMatchingList(null, null, $groupby);

COperation::massCountActes($operations);

foreach ($operations as $_operation) {
  if (array_key_exists($_operation->chir_id, $listPermPrats)) {
    $listPrats[$_operation->chir_id] = $listPermPrats[$_operation->chir_id];
  }
}
$listPrats = CMbArray::pluck($listPrats, "_view");
asort($listPrats);

// Selection des plages opératoires de la journée
$praticien = new CMediusers;
if ($praticien->load($praticien_id)) {
  $praticien->loadRefsForDay($date, true);
  foreach ($praticien->_ref_plages as $plage) {
    $plage->loadRefsNotes();
  }
}

foreach ($praticien->_ref_plages as $plage) {
  foreach ($plage->_ref_operations as $key => $op) {
    $op->countActes();
    if ($op->sortie_salle && $hide_finished == 1 && $praticien->_ref_plages) {
      unset($plage->_ref_operations[$key]);
    }
  }
  foreach ($plage->_unordered_operations as $key => $op) {
    $op->countActes();
    if ($op->sortie_salle && $hide_finished == 1 && $praticien->_ref_plages) {
      unset($plage->_unordered_operations[$key]);
    }
  }
}

foreach ($praticien->_ref_deplacees as $key => $op) {
  $op->countActes();
  if ($op->sortie_salle && $hide_finished == 1 && $praticien->_ref_plages) {
    unset($praticien->_ref_deplacees[$key]);
  }
}

foreach ($praticien->_ref_urgences as $key => $op) {
  $op->countActes();
  if ($op->sortie_salle && $hide_finished == 1 && $praticien->_ref_plages) {
    unset($praticien->_ref_urgences[$key]);
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("vueReduite"    , false        );
$smarty->assign("hide_finished" , $hide_finished);
$smarty->assign("praticien"     , $praticien   );
$smarty->assign("salle"         , new CSalle   );
$smarty->assign("listBlocs"     , $listBlocs   );
$smarty->assign("listPrats"     , $listPrats   );
$smarty->assign("date"          , $date        );
$smarty->assign("operation_id"  , $operation_id);

$smarty->display("inc_liste_op_prat.tpl");
