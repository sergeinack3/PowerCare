<?php
/**
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Developpement\CModelGraph;

CCanDo::checkRead();

$class_select = CView::get("class_select", "str default|CPatient");

CView::checkin();

$class_select = (class_exists($class_select)) ? $class_select : "CPatient";

// Création d'une instance de CModelGraph et initialisation
$data_model = new CModelGraph();
$data_model->init(
  array(
    'class_select'   => $class_select,
    'hierarchy_sort' => "basic",
    'number'         => 1
  )
);

$smarty = new CSmartyDP();
$smarty->assign("data_model", $data_model);
$smarty->display("vw_data_model.tpl");