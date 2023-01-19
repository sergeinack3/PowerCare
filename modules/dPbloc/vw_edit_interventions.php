<?php
/**
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Bloc\CPlageOp;

/**
 * dPbloc
 */
CCanDo::checkEdit();

if (!($plageop_id = CValue::getOrSession("plageop_id"))) {
  CAppUI::setMsg("Vous devez choisir une plage op�ratoire", UI_MSG_WARNING);
  CAppUI::redirect("m=dPbloc&tab=vw_edit_planning");
}

// Infos sur la plage op�ratoire
$plage = new CPlageOp();
$plage->load($plageop_id);
if (!$plage->temps_inter_op) {
  $plage->temps_inter_op = "00:00:00";
}
$plage->loadRefsFwd();
$plage->loadRefChir()->loadRefFunction();
$plage->loadRefAnesth()->loadRefFunction();
$plage->loadRefsNotes();

// Gestion multi-salles (limit� � 2 salles sur la journ�e de la plage actuellement visualis�e)
$multi_salle = array();
if ($plage->chir_id) {
  $multi_salle[] = $plage->salle_id;

  $seconde_plage = CPlageOp::findSecondePlageChir($plage);

  if ($seconde_plage->_id) {
    $multi_salle[] = $seconde_plage->salle_id;
  }
}

// Cr�ation du template
$smarty = new CSmartyDP();

$smarty->assign("plage"       , $plage);
$smarty->assign("multi_salle", $multi_salle);

$smarty->display("vw_edit_interventions.tpl");
