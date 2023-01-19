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
// Récupération des options
$class           = CView::get("class", "str");
$show_properties = CView::get("properties", "bool default|1");
$show_backs      = CView::get("backs", "bool default|1");
$show_formfields = CView::get("formFields", "bool default|1");
$show_heritage   = CView::get("heritage", "bool default|1");
$show_refs       = CView::get("show_refs", "bool default|1");

CView::checkin();

// Création d'une instance de CModelGraph et initialisation de ses attributs
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

$smarty = new CSmartyDP();
$smarty->assign("data_model", $data_model);
$smarty->display("inc_data_model.tpl");