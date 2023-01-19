<?php
/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\SalleOp\CProtocoleGestePerop;

CCanDo::checkEdit();
$protocole_geste_perop_id = CView::get("protocole_geste_perop_id", "ref class|CProtocoleGestePerop");
$operation_id             = CView::get("operation_id", "ref class|COperation");
$type                     = CView::get("type", "str default|perop");
CView::checkin();

CAccessMedicalData::logAccess("COperation-$operation_id");

$protocole_geste_perop = new CProtocoleGestePerop();
$protocole_geste_perop->load($protocole_geste_perop_id);

$total = $protocole_geste_perop->loadRefProtocoleGestePeropItemCategories();

$operation = COperation::find($operation_id);

// Lock add new or edit event
$limit_date_min    = null;

if ($operation->entree_reveil && ($type == 'sspi')) {
  $limit_date_min = $operation->entree_reveil;
}

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("protocole_geste_perop" , $protocole_geste_perop);
$smarty->assign("protocole_items_by_cat", $protocole_geste_perop->_ref_protocole_geste_item_by_categories);
$smarty->assign("total"                 , $total);
$smarty->assign("operation_id"          , $operation_id);
$smarty->assign("limit_date_min"        , $limit_date_min);
$smarty->display("inc_list_protocole_geste_perop_items");
