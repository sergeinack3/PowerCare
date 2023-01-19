<?php
/**
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

/**
 * Vue en mosaïque des graphiques
 */

CCanDo::checkEdit();
$list_graph   = CView::get("list_graph", "str");
$grossesse_id = CView::get("grossesse_id", "ref class|CGrossesse");
CView::checkin();

$array_graph = explode("|", $list_graph);

$smarty = new CSmartyDP();
$smarty->assign("array_graph"         , $array_graph);
$smarty->assign("list_graph"          , $list_graph);
$smarty->assign("grossesse_id"        , $grossesse_id);
$smarty->assign("show_select_children", 1);
$smarty->display("inc_vw_grossesse_graph_mos");
