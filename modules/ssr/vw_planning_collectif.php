<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CTrameSeanceCollective;

global $g, $m;
$function_id   = CView::get("function_id", "ref class|CFunctions", true);
$show_inactive = CView::get("show_plage_inactive", "bool default|0", true);
CView::checkin();

// Chargement de la liste des utilisateurs possibles du planning
$kines     = CEvenementSSR::loadRefExecutants($g);
$functions = CMbObject::massLoadFwdRef($kines, 'function_id');

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
$smarty->assign("functions", $functions);
$smarty->assign("function_id", $function_id);
$smarty->assign("trames", $trames);
$smarty->assign("show_inactive", $show_inactive);
$smarty->display("vw_planning_collectif");
