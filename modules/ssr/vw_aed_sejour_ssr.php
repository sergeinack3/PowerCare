<?php
/**
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ssr\CBilanSSR;

CCanDo::checkRead();
global $m, $current_m;

if (!isset($current_m)) {
  $current_m = CValue::setSession("current_m", $m);
}

$group = CGroups::loadCurrent();

$sejour_id     = CValue::getOrSession("sejour_id");
$view_form_ssr = CValue::get("view_form_ssr", 1);

$user  = CMediusers::get();
$prats = $view_form_ssr ? $user->loadPraticiens(PERM_READ) : array();

$sejour = new CSejour();
$sejour->load($sejour_id);

CAccessMedicalData::logAccess($sejour);

$sejour->loadRefsNotes();
$sejour->loadRefsDocItems();
$sejour->loadSurrAffectations();
$sejour->_ref_prev_affectation->updateView();
$sejour->_ref_curr_affectation->updateView();
$sejour->_ref_next_affectation->updateView();

if ($sejour_id && !$sejour->_id) {
  CAppUI::setMsg(CAppUI::tr("CSejour-unavailable"), UI_MSG_WARNING);
  CAppUI::redirect("m=ssr&tab=vw_aed_sejour&sejour_id=0");
}

$patient = new CPatient();
$bilan   = new CBilanSSR();
if ($sejour->_id) {
  $sejour->loadRefPatient();
  $sejour->loadNDA();

  // Chargement du patient
  $patient = $sejour->_ref_patient;
  $patient->loadIPP();

  // Bilan SSR  
  $bilan->sejour_id = $sejour->_id;
  $bilan->loadMatchingObject();
}
else {
  $sejour->group_id      = $group->_id;
  $sejour->praticien_id  = $user->_id;
  $sejour->entree_prevue = CMbDT::date() . " 08:00:00";
  $sejour->sortie_prevue = CMbDT::date() . " 18:00:00";
  $sejour->recuse        = CAppUI::conf("ssr recusation use_recuse") ? -1 : 0;
}

// Dossier médical visibile ?
$as_can_view_planif       = CAppUI::conf("ssr general as_can_view_planif", $group);
$can_view_dossier_medical = $user->isMedical() || ($as_can_view_planif && $user->isAideSoignant());

// Création du template
$smarty = new CSmartyDP("modules/ssr");

$smarty->assign("can_view_dossier_medical", $can_view_dossier_medical);
$smarty->assign("today", CMbDT::date());
$smarty->assign("sejour", $sejour);
$smarty->assign("bilan", $bilan);
$smarty->assign("patient", $patient);
$smarty->assign("prats", $prats);
$smarty->assign("view_form_ssr", $view_form_ssr);
$smarty->assign("current_m", $current_m);

$smarty->display("vw_aed_sejour_ssr");
