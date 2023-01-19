<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkRead();
$operation_id       = CView::get("operation_id", 'ref class|COperation');
$line_guid          = CView::get("line_guid", 'str');
$planif_id_selected = CView::get("planif_id_selected", "ref class|CPlanificationSysteme");
$type               = CView::get("type", "str default|perop");
$datetime            = CView::get("datetime", "dateTime");
$administration_guid = CView::get("administration_guid", "str");
CView::checkin();

$operation = new COperation();
$operation->load($operation_id);

CAccessMedicalData::logAccess($operation);

$sejour = $operation->loadRefSejour();

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("sejour"             , $sejour);
$smarty->assign("operation"          , $operation);
$smarty->assign("line_guid"          , $line_guid);
$smarty->assign("planif_id_selected" , $planif_id_selected);
$smarty->assign("type"               , $type);
$smarty->assign("datetime"           , $datetime);
$smarty->assign("administration_guid", $administration_guid);
$smarty->display("inc_vw_surveillance_perop_administration");
