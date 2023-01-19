<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$function_id = CView::getRefCheckRead("function_id", "ref class|CFunctions", true);
$date        = CView::get("date", "date default|now", true);
$mode        = CView::get("mode", "enum list|day|week default|day", true);

CView::checkin();

$curr_user = CMediusers::get();

if (!$function_id && $curr_user->isPraticien()) {
  $function_id = $curr_user->function_id;
}

$function = new CFunctions();
$functions = $function->loadSpecialites(PERM_EDIT);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("functions"  , $functions);
$smarty->assign("function_id", $function_id);
$smarty->assign("date"       , $date);
$smarty->assign("mode"       , $mode);

$smarty->display("vw_plages_ressources.tpl");
