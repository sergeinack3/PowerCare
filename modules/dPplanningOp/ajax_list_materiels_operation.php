<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\CConsommationMateriel;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\Stock\CProductOrderItemReception;

CCanDo::checkEdit();

$operation_id           = CView::get("operation_id", "ref class|COperation");
$materiel_operatoire_id = CView::get("materiel_operatoire_id", "ref class|CMaterielOperatoire");
$mode                   = CView::get("mode", "str");
$readonly               = CView::get("readonly", "bool");

CView::checkin();

$operation = new COperation();
$materiel_operatoire = new CMaterielOperatoire();
$materiels_operatoires = [];

$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

if ($materiel_operatoire_id) {
  $materiel_operatoire->load($materiel_operatoire_id);
  $materiel_operatoire->loadRelatedProduct();

  if ($mode === "consommation") {
    $materiels_operatoires[$materiel_operatoire->_id] = $materiel_operatoire;
  }
}
else {
  $operation->loadRefsMaterielsOperatoires(true, false, true);

  if ($mode === "consommation") {
    $materiels_operatoires = $operation->_refs_materiels_operatoires;
  }
}

if ($mode === "consommation") {
  $reception = new CProductOrderItemReception();
  $dmi_category_id = CAppUI::gconf("dmi CDM product_category_id");

  foreach ($materiels_operatoires as $_materiel_operatoire) {
    if ($_materiel_operatoire->dm_id) {
      if ($_materiel_operatoire->_ref_dm->_ref_product) {
        $_materiel_operatoire->_ref_dm->_ref_product->loadRefsLotsAvailable();
      }
    }

    $consommations = $_materiel_operatoire->loadRefsConsommations();
    CStoredObject::massLoadFwdRef($consommations, "user_id");
    $lots = CStoredObject::massLoadFwdRef($consommations, "lot_id");
    $receptions = CStoredObject::massLoadFwdRef($lots, "order_item_id");
    $references = CStoredObject::massLoadFwdRef($receptions, "reference_id");
    CStoredObject::massLoadFwdRef($references, "societe_id");

    foreach ($consommations as $_consommation) {
      $_consommation->loadRefUser();
      $_consommation->loadRefLot()->loadRefOrderItem()->loadReference()->loadRefSociete();
    }
  }
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("operation", $operation);
$smarty->assign("mode", $mode);
$smarty->assign("readonly", $readonly);
$smarty->assign("consommation", new CConsommationMateriel());

if ($materiel_operatoire_id) {
  $smarty->assign("_materiel_operatoire", $materiel_operatoire);
}

$smarty->display($materiel_operatoire_id ? "inc_line_materiel_operatoire" : "inc_list_materiels_operation");