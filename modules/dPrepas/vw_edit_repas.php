<?php
/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Repas\CMenu;
use Ox\Mediboard\Repas\CRepas;
use Ox\Mediboard\Repas\CTypeRepas;

CCanDo::checkEdit();

$date           = CValue::getOrSession("date", CMbDT::date());
$typerepas_id   = CValue::getOrSession("typerepas_id", null);
$affectation_id = CValue::getOrSession("affectation_id", null);

$affectation = new CAffectation;
$listRepas   = new CMenu;
$typeRepas   = new CTypeRepas;
$repas       = new CRepas;

if (!$affectation->load($affectation_id) || !$typeRepas->load($typerepas_id)) {
  // Pas d'affectation
  CValue::setSession("affectation_id", null);
  CAppUI::setMsg("Veuillez sélectionner une affectation", UI_MSG_ALERT);
  CAppUI::redirect("m=dPrepas&tab=vw_planning_repas");
}
else {
  $affectation->loadRefSejour();
  $affectation->loadRefLit();
  $affectation->_ref_lit->loadCompleteView();
  $canAffectation = $affectation->canDo();

  if (!$canAffectation->read || !$affectation->_ref_sejour->sejour_id || $affectation->_ref_sejour->type == "ambu") {
    // Droit Interdit ou Ambulatoire
    CValue::setSession("affectation_id", null);
    $affectation_id = null;
    if (!$affectation->_canRead) {
      $msg = "Vous n'avez pas les droit suffisant pour cette affectation";
    }
    else {
      $msg = "Vous ne pouvez pas plannifier de repas pour cette affectation";
    }
    CAppUI::setMsg($msg, UI_MSG_ALERT);
    CAppUI::redirect("m=dPrepas&tab=vw_planning_repas");
  }

  // Chargement des Repas
  $listRepas = $listRepas->loadByDate($date, $typerepas_id);

  // Chargement Du Repas
  $affectation->loadMenu($date);
  $repas =& $affectation->_list_repas[$date][$typerepas_id];
}


// Création du template
$smarty = new CSmartyDP();

$smarty->assign("affectation", $affectation);
$smarty->assign("typerepas_id", $typerepas_id);
$smarty->assign("date", $date);
$smarty->assign("listRepas", $listRepas);
$smarty->assign("repas", $repas);
$smarty->assign("typeRepas", $typeRepas);

$smarty->display("vw_edit_repas.tpl");