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
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkRead();

$consult_id = CValue::get("consult_id");

$consult = new CConsultation();
$consult->load($consult_id);

CAccessMedicalData::logAccess($consult);

$consult->countActes();
$consult->loadExtCodesCCAM();
$consult->getAssociationCodesActes();
$consult->loadPossibleActes();

$consult->canDo();

// Chargement des actes NGAP
$consult->loadRefsActesNGAP();

// Initialisation d'un acte NGAP
$acte_ngap = CActeNGAP::createEmptyFor($consult);


// Chargement des règles de codage
$consult->loadRefsCodagesCCAM();
foreach ($consult->_ref_codages_ccam as $_codages_by_prat) {
  foreach ($_codages_by_prat as $_codage) {
    $_codage->loadPraticien()->loadRefFunction();
    $_codage->loadActesCCAM();
    $_codage->getTarifTotal();
    foreach ($_codage->_ref_actes_ccam as $_acte) {
      $_acte->getTarif();
    }
  }
}

if (CModule::getActive('lpp') && CAppUI::gconf('lpp General cotation_lpp')) {
  $consult->loadRefsActesLPP();
  
  foreach ($consult->_ref_actes_lpp as $_acte) {
    $_acte->loadRefExecutant();
    $_acte->_ref_executant->loadRefFunction();
  }

  $acte_lpp = CActeLPP::createFor($consult);
}

$sejour = $consult->loadRefSejour();

if ($sejour->_id) {
  $sejour->loadExtDiagnostics();
  $sejour->loadDiagnosticsAssocies();
}

$listPrats = $listChirs = CConsultation::loadPraticiens(PERM_EDIT);
$listAnesths = CMediusers::get()->loadAnesthesistes();

$user = CMediusers::get();
$user->isPraticien();
$user->isProfessionnelDeSante();

$smarty = new CSmartyDP();

$smarty->assign("consult"       , $consult);
$smarty->assign("acte_ngap"     , $acte_ngap);
$smarty->assign("listPrats"     , $listPrats);
$smarty->assign("listChirs"     , $listChirs);
$smarty->assign("listAnesths"   , $listAnesths);
$smarty->assign('user'         , $user);
if (CModule::getActive('lpp') && CAppUI::gconf('lpp General cotation_lpp')) {
  $smarty->assign('acte_lpp', $acte_lpp);
}

$smarty->display("inc_vw_actes");
