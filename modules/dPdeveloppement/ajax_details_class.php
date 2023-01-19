<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CModelGraph;

CCanDo::checkRead();
// Récupération des options
$class           = CView::get("class", "str");
$show_properties = CView::get("show_properties", "bool default|1");
$show_backs      = CView::get("show_backs", "bool default|1");
$show_formfields = CView::get("show_formfields", "bool default|1");
$show_heritage   = CView::get("show_heritage", "bool default|1");
$show_refs       = CView::get("show_refs", "bool default|1");
CView::checkin();

if (!class_exists($class)) {
  CAppUI::stepAjax("Classe inexistante $class", UI_MSG_ERROR);
}

$data_model = new CModelGraph();
$data_model->init(
  array(
    'show_properties' => $show_properties,
    'show_backs'      => $show_backs,
    'show_formfields' => $show_formfields,
    'show_heritage'   => $show_heritage,
    'show_refs'       => $show_refs,
    'class_select'    => $class
  )
);

/** @var CMbObject $object */
$object    = new $data_model->class_select;
$backprops = $object->getBackProps();

// Récupération des différents champs de la classe (propriétés calculées, héritées et normales)
$tmp        = $data_model->getFields($object);
$plainfield = $tmp["plainfield"];
$formfield  = $tmp["formfield"];
$heritage   = $tmp["heritage"];
$refs       = $tmp["refs"];

$db_specs = $data_model->getDB_Specs($plainfield, $refs, $object);

$smarty = new CSmartyDP();

$smarty->assign("plainfield", $plainfield);
$smarty->assign("formfield", $formfield);
$smarty->assign("backprops", $backprops);
$smarty->assign("heritage", $heritage);
$smarty->assign("db_spec", $db_specs);
$smarty->assign("refs", $refs);
$smarty->assign("data_model", $data_model);

$smarty->display("inc_details_class.tpl");