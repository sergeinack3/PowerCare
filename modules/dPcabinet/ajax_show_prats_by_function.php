<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;

$function_id    = CView::getRefCheckRead("function_id", "ref class|CFunctions", true);
$prats_selected = CValue::sessionAbs("planning_prats_selected");

CView::checkin();

// Praticiens sélectionnés
$listPrat = CConsultation::loadPraticiens(PERM_READ, $function_id, null, true);

$smarty = new CSmartyDP();
$smarty->assign('listPrat'      , $listPrat);
$smarty->assign('prats_selected', $prats_selected);
$smarty->display("inc_show_prats_by_function.tpl");
