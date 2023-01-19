<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\PlanningOp\CMaterielOperatoire;

CCanDo::checkEdit();

$materiel_operatoire_id = CView::get("materiel_operatoire_id", "ref class|CMaterielOperatoire");

CView::checkin();

$materiel_operatoire = new CMaterielOperatoire();
$materiel_operatoire->load($materiel_operatoire_id);

$consommations = $materiel_operatoire->loadRefsConsommations();

CStoredObject::massLoadFwdRef($consommations, "user_id");
$lots = CStoredObject::massLoadFwdRef($consommations, "lot_id");
$receptions = CStoredObject::massLoadFwdRef($lots, "order_item_id");
$references = CStoredObject::massLoadFwdRef($receptions, "reference_id");
CStoredObject::massLoadFwdRef($references, "societe_id");

foreach ($consommations as $_consommation) {
  $_consommation->loadRefUser();
  $_consommation->loadRefLot()->loadRefOrderItem()->loadReference()->loadRefSociete();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_materiel_operatoire", $materiel_operatoire);

$smarty->display("inc_list_consommations");