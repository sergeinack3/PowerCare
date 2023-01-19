<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$listBlocs = CGroups::loadCurrent()->loadBlocs(PERM_READ, null, "nom", array("actif" => "= '1'"));

$filter            = new COperation();
$filter->_date_min = CView::get("_date_min", "date default|" . CMbDT::date("-7 day"), true);
$filter->_date_max = CView::get("_date_max", "date default|now", true);
$blocs_ids         = CView::get("blocs_ids", 'str', true);
$function_id       = CView::get("function_id", "ref class|CFunctions", true);
$praticiens_ids    = CView::get("praticiens_ids", 'str', true);
CView::checkin();

if (count($listBlocs) && !is_array($blocs_ids)) {
  $blocs_ids   = array();
  $blocs_ids[] = reset($listBlocs)->_id;
}

if (!is_array($praticiens_ids)) {
  $praticiens_ids   = array();
}

if (is_array($praticiens_ids)) {
  CMbArray::removeValue(0, $praticiens_ids);
}

$praticien  = new CMediusers();
$praticiens = $praticien->loadPraticiens();

$function  = new CFunctions();
$functions = $function->loadSpecialites();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter"        , $filter);
$smarty->assign("blocs_ids"     , $blocs_ids);
$smarty->assign("listBlocs"     , $listBlocs);
$smarty->assign("praticiens"    , $praticiens);
$smarty->assign("functions"     , $functions);
$smarty->assign("function_id"   , $function_id);
$smarty->assign("praticiens_ids", $praticiens_ids);
$smarty->assign("type_commande" , "bloc");

$smarty->display("vw_idx_materiel.tpl");
