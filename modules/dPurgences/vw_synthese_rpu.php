<?php
/**
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Hospi\CUniteFonctionnelle;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\PlanningOp\CModeEntreeSejour;
use Ox\Mediboard\PlanningOp\CModeSortieSejour;
use Ox\Mediboard\Prescription\CCategoryPrescription;
use Ox\Mediboard\Urgences\CRPU;
use Ox\Mediboard\Urgences\CRPUReservationBox;

CCanDo::checkRead();

$rpu_id = CView::get("rpu_id", "ref class|CRPU", true);

CView::checkin();

$rpu = new CRPU();
$rpu->load($rpu_id);
$rpu->loadRefsAttentes();

$consult = $rpu->loadRefConsult();
$consult->loadRefPlageConsult();

$sejour = $rpu->_ref_sejour;

$sejour->loadRefPatient();
$sejour->loadRefCurrAffectation()->updateView();
$sejour->loadPatientBanner();
$sejour->loadRefEtablissementProvenance();

$group = $sejour->loadRefEtablissement();

list($services, $services_type) = CRPU::loadServices($sejour);

$curr_user = CMediusers::get();

$listResponsables = CAppUI::conf("dPurgences only_prat_responsable") ?
  $curr_user->loadPraticiens(PERM_READ, $group->service_urgences_id, null, true) :
  $curr_user->loadListFromType(null, PERM_READ, $group->service_urgences_id, null, true, true);

$view_mode = ($curr_user->isUrgentiste() || $curr_user->isAdmin()) ? "medical" : "infirmier";

$nb_printers = 0;

if (CModule::getActive("printing")) {
  // Chargement des imprimantes pour l'impression d'étiquettes
  $user_printers = CMediusers::get();
  $function      = $user_printers->loadRefFunction();
  $nb_printers   = $function->countBackRefs("printers");
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("rpu", $rpu);
$smarty->assign("view_mode", $view_mode);
$smarty->assign("services", $services);
$smarty->assign("services_type", $services_type);
$smarty->assign("reservations_box", CRPUReservationBox::loadCurrentReservations());
$smarty->assign("listResponsables", $listResponsables);
$smarty->assign("isPraticien", $curr_user->isPraticien());
$smarty->assign("count_macrocibles", CCategoryPrescription::countMacrocibles());
$smarty->assign("list_mode_entree", CModeEntreeSejour::listModeEntree());
$smarty->assign("list_mode_sortie", CModeSortieSejour::listModeSortie());
$smarty->assign("ufs", CUniteFonctionnelle::getUFs($sejour));
$smarty->assign("blocages_lit", CRPU::getBlocagesLits());
$smarty->assign("nb_printers", $nb_printers);

$smarty->display("vw_synthese_rpu");
