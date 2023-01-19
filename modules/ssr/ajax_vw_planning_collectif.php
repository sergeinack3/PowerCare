<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CTrameSeanceCollective;

global $g, $m;

$function_id   = CView::get("function_id", "ref class|CFunctions", true);
$show_inactive = CView::get("show_plage_inactive", "bool default|0", true);
CView::checkin();

$trames = array();
if ($function_id) {
  $trame              = new CTrameSeanceCollective();
  $trame->function_id = $function_id;
  $trame->type        = $m;
  $trame->group_id    = $g;
  $trames             = $trame->loadMatchingList("nom");
}

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("trames", $trames);
$smarty->assign("show_inactive", $show_inactive);
$smarty->display("vw_planning_collectif_trame");
