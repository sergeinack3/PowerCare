<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation = new COperation();

$date                           = CView::get("date", "date default|" . CMbDT::date());

$operation->_prepa_dt_min       = CView::get("_prepa_dt_min", ["dateTime", "default" => "$date 00:00:00"], true);
$operation->_prepa_dt_max       = CView::get("_prepa_dt_max", ["dateTime", "default" => "$date 23:59:59"], true);
$operation->_prepa_chir_id      = CView::get("_prepa_chir_id", "ref class|CMediusers", true);
$operation->_prepa_spec_id      = CView::get("_prepa_spec_id", "ref class|CFunctions", true);
$operation->_prepa_bloc_id      = CView::get("_prepa_bloc_id", "ref class|CBlocOperatoire", true);
$operation->_prepa_salle_id     = CView::get("_prepa_salle_id", "ref class|CSalle", true);
$operation->_prepa_urgence      = CView::get("_prepa_urgence", "bool default|0", true);
$operation->_prepa_libelle      = CView::get("_prepa_libelle", "str", true);
$operation->_prepa_libelle_prot = CView::get("_prepa_libelle_prot", "str", true);
$spec_prepa_type_intervention = [
  "enum",
  "list"    => "hors_plage|avec_plage|tous",
  "default" => "tous"
];
$operation->_prepa_type_intervention = CView::get("_prepa_type_intervention", $spec_prepa_type_intervention, true);

CView::checkin();

$operation->loadRefPrepaChir();
$operation->loadRefPrepaSpec();

$bloc = new CBlocOperatoire();
$blocs = $bloc->loadGroupList();

$smarty = new CSmartyDP();

$smarty->assign("operation", $operation);
$smarty->assign("blocs", $blocs);

$smarty->display("vw_preparation_sterilisation");
