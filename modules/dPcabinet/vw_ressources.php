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

$function_id = CView::getRefCheckEdit("function_id", "ref class|CFunctions", true);

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

$smarty->display("vw_ressources.tpl");
