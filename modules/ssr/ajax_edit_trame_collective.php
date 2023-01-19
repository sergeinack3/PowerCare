<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbObject;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Ssr\CEvenementSSR;
use Ox\Mediboard\Ssr\CTrameSeanceCollective;

global $g, $m;

CCanDo::checkEdit();
$trame_id    = CView::get("trame_id", "ref class|CTrameSeanceCollective");
$function_id = CView::get("function_id", "ref class|CFunctions", true);
CView::checkin();

$trame = new CTrameSeanceCollective();
$trame->load($trame_id);

if (!$trame->_id) {
  $trame->group_id    = $g;
  $trame->function_id = $function_id;
  $trame->type        = $m;
}

// Chargement de la liste des utilisateurs possibles du planning
$kines     = CEvenementSSR::loadRefExecutants($g);
$functions = CMbObject::massLoadFwdRef($kines, 'function_id');

// Création du template
$smarty = new CSmartyDP("modules/ssr");
$smarty->assign("trame", $trame);
$smarty->assign("functions", $functions);
$smarty->display("vw_edit_trame_collective");
