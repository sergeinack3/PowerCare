<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Maternite\CNaissance;

CCanDo::checkEdit();

$affectation_id = CView::get("affectation_id", "ref class|CAffectation");
$lit_id         = CView::get("lit_id", "ref class|CLit");
$urgence        = CView::get("urgence", "bool");
$mod_urgence    = CView::get("mod_urgence", "bool default|0");
$from_tempo     = CView::get("from_tempo", "bool default|0");

CView::checkin();

$affectation = new CAffectation();
$affectation->load($affectation_id);
$lit = new CLit();
$lit->load($affectation->lit_id);
if ($urgence) {
  $service_urgence          = CGroups::loadCurrent()->service_urgences_id;
  $affectation->function_id = $service_urgence;
}

$sejour_maman = null;

if (CModule::getActive("maternite") && $affectation->sejour_id) {
  $naissance                   = new CNaissance();
  $naissance->sejour_enfant_id = $affectation->sejour_id;
  $naissance->loadMatchingObject();

  if ($naissance->_id) {
    $sejour_maman = $naissance->loadRefSejourMaman();
    $sejour_maman->loadRefPatient();
  }
}

if ($affectation->_id) {
  $affectation->loadRefSejour()->loadRefPatient();
}
else {
  $affectation->lit_id = $lit_id;
  $lit->load($lit_id);
  $lit->loadRefChambre()->loadRefService();
  $affectation->entree = CMbDT::dateTime();
}

$smarty = new CSmartyDP();

$smarty->assign("affectation", $affectation);
$smarty->assign("lit", $lit);
$smarty->assign("lit_id", $lit_id);
$smarty->assign("sejour_maman", $sejour_maman);
$smarty->assign("urgence", $urgence);
$smarty->assign("from_tempo", $from_tempo);
$smarty->assign("mod_urgence", $mod_urgence);

$smarty->display("inc_edit_affectation.tpl");
