<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\PlanningOp\COperation;

CCanDo::checkEdit();

$operation = new COperation();
$now = CMbDT::date();

$operation->_prepa_dt_min            = CView::get("_prepa_dt_min", ["dateTime", "default" => "$now 00:00:00"], true);
$operation->_prepa_dt_max            = CView::get("_prepa_dt_max", ["dateTime", "default" => "$now 23:59:59"], true);
$operation->_prepa_chir_id           = CView::get("_prepa_chir_id", "ref class|CMediusers", true);
$operation->_prepa_spec_id           = CView::get("_prepa_spec_id", "ref class|CFunctions", true);
$operation->_prepa_bloc_id           = CView::get("_prepa_bloc_id", "ref class|CBlocOperatoire", true);
$operation->_prepa_salle_id          = CView::get("_prepa_salle_id", "ref class|CSalle", true);
$operation->_prepa_urgence           = CView::get("_prepa_urgence", "bool default|0", true);
$operation->_prepa_libelle           = CView::get("_prepa_libelle", "str", true);
$operation->_prepa_libelle_prot      = CView::get("_prepa_libelle_prot", "str", true);
$operation->_prepa_order_col         = CView::get("_prepa_order_col", "str default|_patient_id", true);
$operation->_prepa_order_way         = CView::get("_prepa_order_way", "str default|ASC", true);
$operation->_filter_panier           = CView::get("_filter_panier", "str", true);
$operation->_prepa_type_intervention = CView::get("_prepa_type_intervention",
                                                  "enum list|hors_plage|avec_plage|tous default|tous", true);

CView::checkin();

$operation->loadRefPrepaChir();
$operation->loadRefPrepaSpec();

// Chargement des blocs et salles
$bloc = new CBlocOperatoire();
$blocs = $bloc->loadGroupList();

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("operation", $operation);
$smarty->assign("blocs", $blocs);

$smarty->display("vw_preparation_salles");
