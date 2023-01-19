<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultAnesth;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Moebius\CMoebiusAPI;

CCanDo::checkEdit();

$prat_id           = CView::get("chirSel", "ref class|CMediusers");
$dossier_anesth_id = CView::get("dossier_anesth_id", "ref class|CConsultAnesth");
$consult_id        = CView::get("selConsult", "ref class|CConsultation");
$represcription    = CView::get("represcription", "num default|0");

CView::checkin();

$user = CMediusers::findOrFail($prat_id);
$user->needsEdit();

if (!$user->isMedical()) {
  CAppUI::setMsg("Vous devez selectionner un professionnel de santé", UI_MSG_ALERT);
  CAppUI::redirect("m=dPcabinet&tab=0");
}

$consult = CConsultation::findOrNew($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->_ref_chir = $user;

if ($consult->_id) {
  $consult->needsEdit();
  $consult->loadRefsFwd();

  $consult_anseth = $consult->loadRefConsultAnesth();

  foreach ($consult->_refs_dossiers_anesth as $_dossier) {
    $_dossier->loadRefConsultation();
    $_dossier->loadRefOperation()->loadRefPlageOp();
  }

  if ($dossier_anesth_id) {
    $consult_anseth               = $consult->_refs_dossiers_anesth[$dossier_anesth_id];
    $consult->_ref_consult_anesth = $consult_anseth;
  }

  if ($consult_anseth->_id) {
    $consult_anseth->_ref_consultation->loadRefPraticien();
    $consult_anseth->_ref_operation->loadRefChir(true);
    $dossier_medical = $consult_anseth->_ref_sejour->loadRefDossierMedical();
    $dossier_medical->countAntecedents(false);
    $consult_anseth->_ref_sejour->loadRefPraticien(true);
  }

  $patient =& $consult->_ref_patient;
  $patient->loadRefsSejours();
  foreach ($patient->_ref_sejours as $_sejour) {
    $_sejour->loadRefsOperations();

    foreach ($_sejour->_ref_operations as $_operation) {
      $_operation->loadRefsConsultAnesth();
      $_operation->loadRefPlageOp(true);
      $_operation->loadRefChir(true);
    }
  }
}
else {
  $consult->_ref_consult_anesth = new CConsultAnesth();
}

$consult_anesth    =& $consult->_ref_consult_anesth;
$next_stay_surgery = $patient->getNextSejourAndOperation($consult->_ref_plageconsult->date, true, $consult->_id);

if (CModule::getActive("maternite")) {
  $patient->getNextGrossesse($consult->_ref_plageconsult->date);
}

$list_practitioners = $user->loadPraticiens(PERM_READ);

if (CModule::getActive("moebius") && CAppUI::pref("ViewConsultMoebius")) {
  $moebius_dhes    = CMoebiusAPI::getListDHEProtocols();
  $moebius_consult = CMoebiusAPI::getId400Protocole($consult_anesth);

  if ($moebius_consult->_id_moebius_dhe) {
    foreach ($moebius_dhes as $_moebius_dhe) {
      if ($_moebius_dhe["id"] == $moebius_consult->_id_moebius_dhe->id400) {
        $moebius_consult->_id_moebius_dhe->_view = $_moebius_dhe["name"];
      }
    }
  }
}

$smarty = new CSmartyDP();

$smarty->assign("represcription", $represcription);
$smarty->assign("consult", $consult);
$smarty->assign("consult_anesth", $consult_anesth);
$smarty->assign("patient", $patient);
$smarty->assign("nextSejourAndOperation", $next_stay_surgery);
$smarty->assign("listChirs", $list_practitioners);

if (CModule::getActive("moebius") && CAppUI::pref("ViewConsultMoebius")) {
  $smarty->assign("id_moebius_dhe", $moebius_consult->_id_moebius_dhe);
}

$smarty->display("inc_consult_anesth/interventions.tpl");
