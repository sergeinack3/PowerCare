<?php
/**
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

global $m, $tab;

$module = CView::get("module", 'str');
if (!$module) {
  $module = $m;
}

$canUnlockActes = $module == "dPpmsi" || CModule::getCanDo("dPsalleOp")->admin;

if (null == $object_class = CView::get("object_class", 'str')) {
  CAppUI::stepMessage(UI_MSG_WARNING, "$tab-msg-mode-missing");
  return;
}

$NDA = "";
$IPP = "";

$confirmCloture = CView::get("confirmCloture", 'bool default|0');
$object_id = CView::get("object_id", 'num', true);
$mb_operation_id = CView::post('mb_operation_id', "num default|$object_id");
$mb_sejour_id = CView::post('mb_sejour_id', "num default|$object_id");

CView::checkin();

$group_guid = null;
switch ($object_class) {
  case "COperation":
    $object = new COperation();

    // Chargement de l'opération et génération du document
    $operation_id = $mb_operation_id;
    if ($object->load($operation_id)) {
      $object->loadRefSejour();

      $mbSejour = $object->_ref_sejour;
      $mbSejour->loadNDA();
      $group_guid = $mbSejour->loadRefEtablissement()->_guid;
      $NDA = $mbSejour->_NDA;
      $mbSejour->loadRefPatient();
      $mbSejour->_ref_patient->loadIPP();
      $IPP = $mbSejour->_ref_patient->_IPP;
    }
    break;
  case "CSejour":
    $object = new CSejour();

    // Chargement du séjour et génération du document
    $sejour_id = $mb_sejour_id;
    if ($object->load($sejour_id)) {
      $object->loadRefPatient();
      $object->loadRefDossierMedical();
      $object->loadNDA();
      $group_guid = $object->loadRefEtablissement()->_guid;
      $NDA = $object->_NDA;
      $object->_ref_patient->loadIPP();
      $IPP = $object->_ref_patient->_IPP;
    }
    break;
  default:
    $object = new $object_class();
}

$object->countExchanges("pmsi", "evenementServeurActe");

// Création du template
$smarty = new CSmartyDP("modules/dPpmsi");
$smarty->assign("canUnlockActes", $canUnlockActes);
$smarty->assign("object", $object);
$smarty->assign("IPP"   , $IPP);
$smarty->assign("NDA"   , $NDA);
$smarty->assign("module", $module);
$smarty->assign("confirmCloture", $confirmCloture);
$smarty->assign("group_guid"    , $group_guid);
$smarty->display("inc_export_actes_pmsi");
