<?php
/**
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CAffectation;
use Ox\Mediboard\Hospi\CLit;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

// Récupération des paramètres
$curr_affectation_id = CView::get("curr_affectation_id", "ref class|CAffectation");
$uf_medicale         = CView::get("uf_medicale_id", "ref class|CUniteFonctionnelle");
$lit_id              = CView::get("lit_id", "ref class|CLit");
$see_validate        = CView::get("see_validate", "bool default|1");

CView::checkin();

$lit = new CLit();
$lit->load($lit_id);

$mediuser = CMediusers::get();
$mediuser->loadRefFunction();

/** @var CAffectation $affectation */
$affectation = new CAffectation();
$affectation->load($curr_affectation_id);
$affectation->loadRefUfs();
$sejour         = $affectation->loadRefSejour();
$praticien      = $sejour->loadRefPraticien();
$prat_placement = $affectation->loadRefPraticien();
$function       = $praticien->loadRefFunction();

if (!$prat_placement->_id) {
  $prat_placement = $praticien;
}

$praticiens = array();
if ($uf_medicale) {
  $praticiens = CAffectation::loadPraticiensUfMedicale($uf_medicale);
}
else {
  $praticiens = $mediuser->loadPraticiens(PERM_EDIT, $function->_id);
}

foreach ($praticiens as $prat) {
  $prat->loadRefFunction();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("affectation", $affectation);
$smarty->assign("lit", $lit);
$smarty->assign("praticien", $praticien);
$smarty->assign("prat_placement", $prat_placement);
$smarty->assign("praticiens", $praticiens);
$smarty->assign("see_validate", $see_validate);

$smarty->display("inc_vw_select_prat_uf.tpl");
