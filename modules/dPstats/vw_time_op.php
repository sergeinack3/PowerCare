<?php
/**
 * @package Mediboard\Stats
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CUser;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CSejour;

date_default_timezone_set("UTC");

CCanDo::checkRead();
$user = CUser::get();

$typeVue        = CValue::getOrSession("typeVue", 2);
$nb_sejour_mini = CValue::getOrSession("nb_sejour_mini", 3);

$user      = new CMediusers;
$listPrats = $user->loadPraticiens(PERM_READ);

if ($typeVue == 0) {
  // Stat des temps de préparation
  include "inc_vw_timeop_op.php";
}
elseif ($typeVue == 1) {
  // Stat des temps opératoires
  include "inc_vw_timeop_prepa.php";
}
else {
  // Stat des temps d'hospitalisation
  include "inc_vw_timehospi.php";
}


// Création du template
$smarty = new CSmartyDP();

if ($typeVue == 0 || $typeVue == 2) {
  $smarty->assign("prat_id", $prat_id);
  $smarty->assign("codeCCAM", $codeCCAM);
  $smarty->assign("listPrats", $listPrats);
}

if ($typeVue == 2) {
  $sejour     = new CSejour;
  $listHospis = $sejour->_specs["type"]->_locales;
  unset($listHospis["exte"]);
  $smarty->assign("listHospis", $listHospis);
  $smarty->assign("type", $type);
}

$smarty->assign("user_id", $user->_id);
$smarty->assign("listTemps", $listTemps);
$smarty->assign("total", $total);
$smarty->assign("typeVue", $typeVue);
$smarty->assign("nb_sejour_mini", $nb_sejour_mini);

$smarty->display("vw_time_op.tpl");
