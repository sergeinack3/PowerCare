<?php
/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\PlanningOp\CSejour;

CCanDo::checkAdmin();

$sejour = new CSejour();
$group = CGroups::loadCurrent();

// Supression de patients
$suppr    = 0;
$error    = 0;
$qte      = CValue::get("qte", 1);
$date_min = CValue::get("date_min", CMbDT::date());
$date_min = $date_min ? $date_min : CMbDT::date();
$where = array("entree" => ">= '$date_min 00:00:00'",
               "group_id" => "= '$group->_id'");
$listSejours = $sejour->loadList($where, null, $qte);

foreach ($listSejours as $_sejour) {
  CAppUI::setMsg($_sejour->_view, UI_MSG_OK);
  if ($msg = $_sejour->purge()) {
    CAppUI::setMsg($msg, UI_MSG_ALERT);
    $error++;
    continue;
  }
  CAppUI::setMsg("séjour supprimé", UI_MSG_OK);
  $suppr++;
}

// Nombre de patients
$nb_sejours = $sejour->countList($where);

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("resultsMsg" , CAppUI::getMsg());
$smarty->assign("suppr"      , $suppr);
$smarty->assign("error"      , $error);
$smarty->assign("nb_sejours" , $nb_sejours);

$smarty->display("inc_purge_sejours");
