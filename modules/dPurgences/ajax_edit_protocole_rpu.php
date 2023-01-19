<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CService;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\Urgences\CProtocoleRPU;

CCanDo::checkEdit();

$protocole_rpu_id = CView::get("protocole_rpu_id", "ref class|CProtocoleRPU");

CView::checkin();

$group     = CGroups::get();
$curr_user = CMediusers::get();

$protocole_rpu = new CProtocoleRPU();

if (!$protocole_rpu->load($protocole_rpu_id)) {
  $protocole_rpu->group_id = $group->_id;
}

$protocole_rpu->countContextDocItems();

$urgentistes = CAppUI::conf("dPurgences only_prat_responsable") ?
  $curr_user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true) :
  $curr_user->loadListFromType(null, PERM_READ, $group->service_urgences_id, null, true, true);

$services = array();
if (CAppUI::conf("dPurgences view_rpu_uhcd")) {
  // Affichage des services UHCD et d'urgence
  $services = CService::loadServicesUHCDRPU();
}
else {
  $services = CService::loadServicesUrgence() + CService::loadServicesUHCD();
}
$contrainteProvenance[7] = array("", 1, 2, 3, 4, 6);
$contrainteProvenance[8] = array("", 5, 8);

// Contraintes sur le mode de sortie / destination
$contrainteDestination["mutation"]  = array("", 1, 2, 3, 4);
$contrainteDestination["transfert"] = array("", 1, 2, 3, 4);
$contrainteDestination["normal"]    = array("", 6, 7);

$smarty = new CSmartyDP();

$smarty->assign("protocole_rpu", $protocole_rpu);
$smarty->assign("urgentistes", $urgentistes);
$smarty->assign("ufs", CUniteFonctionnelle::getUFs());
$smarty->assign("services", $services);
$smarty->assign("contrainteProvenance" , $contrainteProvenance);
$smarty->assign("contrainteDestination", $contrainteDestination);
$smarty->assign("list_mode_entree", CModeEntreeSejour::listModeEntree());

$smarty->display("inc_edit_protocole_rpu");