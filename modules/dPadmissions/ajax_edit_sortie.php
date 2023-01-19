<?php
/**
 * @package Mediboard\Admissions
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::check();

$sejour_id            = CView::get("sejour_id", "ref class|CSejour");
$module               = CView::get("module", "str");
$callback             = CView::get("callback", "str");
$modify_sortie_prevue = CView::get("modify_sortie_prevue", "bool default|1");

CView::checkin();

$sejour = new CSejour();
$sejour->load($sejour_id);

$can_admission = CModule::getCanDo("dPadmissions");

if (!$sejour->canDo()->edit && !$can_admission->edit
    && !CModule::getCanDo("dPhospi")->edit
    && !CModule::getCanDo("dPurgences")->edit
    && !CModule::getCanDo("soins")->edit
) {
  $can_admission->denied();
}

$sejour->loadRefServiceMutation();
$sejour->loadRefEtablissementTransfert();
$sejour->loadRefsOperations();

// Cas des urgences
if (CModule::getActive("dPurgences")) {
  $sejour->loadRefRPU()->loadRefSejourMutation();
}

$sejour->loadRefPatient()->loadIPP();

if (CModule::getActive("maternite")) {
  if (!$sejour->_ref_patient->checkAnonymous()) {
    $sejour->loadRefsNaissances();
    foreach ($sejour->_ref_naissances as $_naissance) {
      $_naissance->loadRefSejourEnfant()->loadRefPatient();
    }
    $sejour->_sejours_enfants_ids = CMbArray::pluck($sejour->_ref_naissances, "sejour_enfant_id");
  }

  $sejour_mere = $sejour->loadRefNaissance()->loadRefSejourMaman();
  if ($sejour_mere->_id) {
    $sejour_mere->loadRefPatient();
    if ($sejour_mere->_ref_patient->checkAnonymous()) {
      $sejour_mere = new CSejour();
    }
    else {
      $sejour_mere->loadRefsNaissances();
      foreach ($sejour_mere->_ref_naissances as $_naissance_mere) {
        if ($_naissance_mere->sejour_enfant_id === $sejour->_id) {
          unset($sejour_mere->_ref_naissances[$_naissance_mere->_id]);
          continue;
        }
        $_naissance_mere->loadRefSejourEnfant()->loadRefPatient();
      }
      $sejour_mere->_sejours_enfants_ids = CMbArray::pluck($sejour_mere->_ref_naissances, "sejour_enfant_id");
    }
  }

}
else {
  $sejour_mere = new CSejour();
}

// Cas du mode sortie personnalisé
$list_mode_sortie = array();
if (CAppUI::conf("dPplanningOp CSejour use_custom_mode_sortie")) {
  $mode_sortie = new CModeSortieSejour();
  $where       = array(
    "actif" => "= '1'",
  );
  $list_mode_sortie = $mode_sortie->loadGroupList($where);

  CStoredObject::massLoadFwdRef($list_mode_sortie, "etab_externe_id");

  foreach ($list_mode_sortie as $_mode_sortie) {
    $_mode_sortie->loadRefEtabExterne();
  }
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("callback"            , stripslashes($callback));
$smarty->assign("modify_sortie_prevue", $modify_sortie_prevue);
$smarty->assign("sejour"              , $sejour);
$smarty->assign("module"              , $module);
$smarty->assign("list_mode_sortie"    , $list_mode_sortie);
$smarty->assign("sejour_mere"         , $sejour_mere);
$smarty->display("inc_edit_sortie");
