<?php
/**
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\GestionCab\CEmployeCab;
use Ox\Mediboard\GestionCab\CParamsPaie;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$employecab_id = CValue::getOrSession("employecab_id", null);

$user = CMediusers::get();

$employe = new CEmployeCab();
$where = array();
$where["function_id"] = "= '$user->function_id'";

$listEmployes = $employe->loadList($where);
if ($employecab_id) {
  $employe =& $listEmployes[$employecab_id];
}
else {
  $employe->function_id = $user->function_id;
}

$paramsPaie = new CParamsPaie();
if ($employe->employecab_id) {
  $paramsPaie->loadFromUser($employe->employecab_id);
  $paramsPaie->loadRefsFwd();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("employe"      , $employe);
$smarty->assign("paramsPaie"   , $paramsPaie);
$smarty->assign("listEmployes" , $listEmployes);

$smarty->display("edit_params");
