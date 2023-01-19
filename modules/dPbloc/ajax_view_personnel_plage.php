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
use Ox\Core\CView;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$plageop_id = CView::get("plageop_id", "ref class|CPlageOp", true);

CView::checkin();

// Infos sur la plage opératoire
$plage = new CPlageOp();

if (!$plage->load($plageop_id)) {
  CAppUI::setMsg("Vous devez choisir une plage opératoire", UI_MSG_WARNING);
  CAppUI::redirect("m=dPbloc&tab=vw_edit_planning");
}

if (!$plage->temps_inter_op) {
  $plage->temps_inter_op = "00:00:00";
}

// liste des anesthesistes
$mediuser = new CMediusers();
$listAnesth = $mediuser->loadListFromType(array("Anesthésiste"));

// Chargement du personnel
$listPers = $plage->loadPersonnelDisponible(null, true);
$affectations_plage = $plage->_ref_affectations_personnel;

// Création du template
$smarty = new CSmartyDP();
$smarty->assign("affectations_plage", $affectations_plage);
$smarty->assign("listPers"          , $listPers);
$smarty->assign("listAnesth"        , $listAnesth);
$smarty->assign("plage"             , $plage);

$smarty->display("inc_view_personnel_plage.tpl");
