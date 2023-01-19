<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CTypeAnesth;

CCanDo::checkRead();
$intervention_id = CView::get("intervention_id", "ref class|COperation");
$modif_operation = CView::get("modif_operation", "bool");
$show_cormack    = CView::get("show_cormack", "num default|1");
CView::checkin();

$intervention = new COperation();
$intervention->load($intervention_id);

CAccessMedicalData::logAccess($intervention);

$consult_anesth = $intervention->loadRefsConsultAnesth();
$consult_anesth->loadRefChir();

$intervention->loadRefAnesth();
$intervention->loadRefSejour()->loadRefPatient()->loadRefLatestConstantes();
$intervention->_ref_sejour->_ref_patient->loadRefDossierMedical();

$sejour = $intervention->_ref_sejour;

if ($sejour->grossesse_id && CModule::getActive("maternite")) {
  $sejour->loadRefGrossesse();
}

$listAnesths    = new CMediusers();
$listAnesthType = new CTypeAnesth();

$listAnesths    = $listAnesths->loadAnesthesistes(PERM_DENY);
$listAnesthType = $listAnesthType->loadGroupList();

$smarty = new CSmartyDP();
$smarty->assign("selOp"          , $intervention);
$smarty->assign("consult_anesth" , $consult_anesth);
$smarty->assign("modif_operation", $modif_operation);
$smarty->assign("listAnesths"    , $listAnesths);
$smarty->assign("listAnesthType" , $listAnesthType);
$smarty->assign("show_cormack"   , $show_cormack);
$smarty->display("inc_vw_infos_intervention");
