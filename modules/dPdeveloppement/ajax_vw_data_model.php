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

// Récupération des paramètres
$class           = CView::get("object_class", "str notNull");
$number          = CView::get("number", "num default|1");
$backsprops_show = CView::get("show_backprops", "enum list|" . implode('|', CModelGraph::$backprops_list) . " default|none");
$hierarchy_sort  = CView::get("hierarchy_sort", "enum list|" . implode('|', CModelGraph::$hierarchy_list) . " default|hubsize");
$show_hover      = CView::get("show_hover", "bool default|1");
$show_props      = CView::get("show_props", "bool default|1");

CView::checkin();

if ($number < 0) {
  $number = 1;
}

if (!class_exists($class)) {
  CAppUI::stepAjax("La classe $class n'existe pas.", UI_MSG_ERROR);
}

// Instanciation de CModelGraph et initialisation avec les valeurs récupérées
$data_model = new CModelGraph();
$data_model->init(
  array(
    'class_select'   => $class,
    'show_backprops' => $backsprops_show,
    'hierarchy_sort' => $hierarchy_sort,
    'number'         => $number,
    'show_hover'     => $show_hover,
    'show_props'     => $show_props
  )
);

// Récupération Hey you !des données qui permettent de créer le graph
$graph = $data_model->getGraph();
$inv_class = array();
foreach ($graph[0]['links'] as $_value) {
  /** @var CMbObject $_class */
  $_class = new $_value;
  $_backs = $_class->getBackProps();
  foreach ($_backs as $_field_name => $_back_value) {
    $val = explode(" ", $_back_value);
    if ($val[0] == $graph[0]["class"]) {
      if (array_key_exists($_value, $inv_class)) {
        $inv_class[$_value] .= "/". $_field_name;
      }
      else {
        $inv_class[$_value] = $_field_name;
      }

    }
  }
}
// Récupération des données qui permettent de créer les backprops (en fonction des choix de l'utilisateur)
$backs      = $data_model->showBackProps();
$back_split = array();
$backs_name = array();
foreach ($backs as $_back) {
  $expl         = explode(" ", $_back);
  $back_split[] = $expl[0];
  $backs_name[$expl[0]] = $expl[1];
}
$back_split = array_unique($back_split);

$smarty = new CSmartyDP();
$smarty->assign("graph", $graph);
$smarty->assign("backs", $back_split);
$smarty->assign("inv_class", $inv_class);
$smarty->assign("backs_name", $backs_name);
$smarty->assign("hierarchy_sort", $data_model->hierarchy_sort);
$smarty->assign("show_hover", $data_model->show_hover);
$smarty->display("inc_draw_graph.tpl");
