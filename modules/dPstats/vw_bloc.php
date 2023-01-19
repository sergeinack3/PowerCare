<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CDiscipline;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkRead();


// Récupération des informations du formulaire

$hors_plage = CValue::get("hors_plage", 1);
$bloc_id    = CValue::get("bloc_id");

$filter            = new COperation();
$filter->_date_min = CValue::get("_date_min", CMbDT::date("-1 YEAR"));
$filter->_date_min = CMbDT::date("FIRST DAY OF THIS MONTH", $filter->_date_min);

$filter->_date_max = CValue::get("_date_max", CMbDT::date());
$filter->_date_max = CMbDT::date("FIRST DAY OF NEXT MONTH", $filter->_date_max);
$filter->_date_max = CMbDT::date("-1 DAY", $filter->_date_max);

$filter->_prat_id    = CValue::get("prat_id");
$filter->_func_id    = CValue::get("func_id");
$filter->salle_id    = CValue::get("salle_id");
$filter->codes_ccam  = strtoupper(CValue::get("codes_ccam", ""));
$filter->_specialite = CValue::get("discipline_id");

$filterSejour       = new CSejour();
$filterSejour->type = CValue::getOrSession("type");

$user      = new CMediusers();
$listPrats = $user->loadPraticiens(PERM_READ);

$listFuncs = array_unique(CMbArray::pluck($listPrats, "_ref_function"));
$order_text = CMbArray::pluck($listFuncs, "text");
array_multisort($order_text, SORT_ASC, $listFuncs);

$bloc               = new CBlocOperatoire();
$listBlocs          = CGroups::loadCurrent()->loadBlocs(PERM_READ, true, "nom", array("actif" => "= '1'"), array("actif" => "= '1'"));
$listBlocsForSalles = $listBlocs;

$bloc->load($bloc_id);
if ($bloc->_id) {
  foreach ($listBlocsForSalles as $key => &$curr_bloc) {
    if ($curr_bloc->_id != $bloc->_id) {
      unset ($listBlocsForSalles[$key]);
    }
  }
}

$listDisciplines = new CDiscipline();
$listDisciplines = $listDisciplines->loadUsedDisciplines();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("filter", $filter);
$smarty->assign("filterSejour", $filterSejour);
$smarty->assign("listPrats", $listPrats);
$smarty->assign("listFuncs", $listFuncs);
$smarty->assign("listBlocs", $listBlocs);
$smarty->assign("listBlocsForSalles", $listBlocsForSalles);
$smarty->assign("bloc", $bloc);
$smarty->assign("listDisciplines", $listDisciplines);
$smarty->assign("hors_plage", $hors_plage);

$smarty->display("vw_bloc.tpl");
