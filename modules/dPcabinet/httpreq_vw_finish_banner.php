<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CCanDo;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

$consult_id = CValue::getOrSession("selConsult");
$user_id    = CValue::getOrSession("chirSel");
$_is_anesth = CValue::get("_is_anesth");

// Utilisateur sélectionné
$user = CMediusers::get($user_id);
$canUser = $user->canDo();
$canUser->needsEdit();

// Liste des praticiens
$listPrats = CConsultation::loadPraticiens(PERM_EDIT);

// Consultation courante
$consult = CConsultation::findOrFail($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->loadRefConsultAnesth();

$canConsult = $consult->canDo();
$canConsult->needsEdit();

$patient = $consult->loadRefPatient();
$patient->loadRefPhotoIdentite();
$patient->loadRefDossierMedical()->loadRefsAntecedents();
$patient->loadRefLatestConstantes(null, [], null, false);

$consult->loadRefPraticien()->loadRefFunction();
$consult->loadRefSejour();
$consult->countDocs();
$consult->countFiles();

if (CModule::getActive("maternite")) {
  $consult->loadRefGrossesse();
}

$rpu = $consult->_ref_sejour->loadRefRPU();
if ($rpu) {
    $rpu->loadRefSejour();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("_is_anesth"     , $_is_anesth);
$smarty->assign("consult"        , $consult);
$smarty->assign("consult_anesth" , $consult->_ref_consult_anesth);
$smarty->assign("listPrats"      , $listPrats);
$smarty->assign("dossier_medical", $patient->_ref_dossier_medical);
$smarty->assign("rpu"            , $rpu);

$smarty->display("inc_finish_banner.tpl");
