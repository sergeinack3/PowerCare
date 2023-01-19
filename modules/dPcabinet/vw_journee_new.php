<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkRead();
$date        = CView::get("date", "date default|now", true);
$function_id = CView::get("function_id", "ref class|CFunctions", true);
CView::checkin();

$group = CGroups::loadCurrent();

$function  = new CFunctions();
$functions = $function->loadListWithPerms(PERM_EDIT, array("group_id" => " = '$group->_id' "), "text");

// Praticiens sélectionnés
$listPrat = CConsultation::loadPraticiens(PERM_EDIT, $function_id, null, true);

$prats_selected = implode("|", array_keys($listPrat));

$smarty = new CSmartyDP();
$smarty->assign("date", $date);
$smarty->assign("function_id", $function_id);
$smarty->assign("functions", $functions);
$smarty->assign("prats_selected", $prats_selected);

$smarty->assign("isCabinet", CAppUI::isCabinet());
$smarty->display("vw_journee_new");
