<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Cabinet\CActeNGAP;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Ccam\CActe;
use Ox\Mediboard\Ccam\CModelCodage;
use Ox\Mediboard\Lpp\CActeLPP;
use Ox\Mediboard\Mediusers\CFunctions;

CCanDo::checkEdit();

$codage_id = CValue::get('codage_id');

$tarif_id = CValue::get('tarif_id');

$chir_id = CValue::get('chir_id');
$function_id = CValue::get('function_id');
$libelle = CValue::get('description');

$codes_ccam = CValue::get('codes_ccam');
$codes_ngap = CValue::get('codes_ngap');
$codes_lpp = CValue::get('codes_lpp');

$codage = new CModelCodage();
if ($codage_id) {
  $codage->load($codage_id);
}
else {
  if ($tarif_id) {
    /** @var CTarif $tarif */
    $tarif = CTarif::loadFromGuid("CTarif-{$tarif_id}");
    $libelle = $tarif->description;
    $chir_id = $tarif->chir_id;
    $function_id = $tarif->function_id;

    $codage->objects_guid = $tarif->_guid;
  }
  else {
    $tarif = new CTarif();
    $tarif->codes_ccam = $codes_ccam;
    $tarif->codes_ngap = $codes_ngap;
    $tarif->codes_lpp = $codes_lpp;

    $tarif->updateFormFields();
  }

  if ($function_id) {
    /** @var CFunctions $function */
    $function = CFunctions::loadFromGuid("CFunctions-{$function_id}");
    $users = $function->loadRefsUsers(array('Chirurgien', 'Médecin', 'Sage Femme', 'Rééducateur', 'Dentiste', 'Anesthésiste'));
    $user = reset($users);
    $chir_id = $user->_id;
  }

  $codage->libelle = $libelle;
  $codage->praticien_id = $chir_id;
  $codage->date = CMbDT::date();

  $tarif->getPrecodeReady();
  $codage->_codes_ccam = array();

  foreach ($tarif->_codes_ccam as $_code) {
    $_code = explode('-', $_code);
    $codage->_codes_ccam[] = $_code[0];
  }

  $codage->codes_ccam = implode('|', $codage->_codes_ccam);
  $codage->store();

  $codage->getActeExecution();
  $tarif->getPrecodeReady();
  $codage->_coded = '0';
  /** @var CActe $_act */
  foreach ($tarif->_new_actes as $_act) {
    $_act->object_class = $codage->_class;
    $_act->object_id = $codage->_id;
    $_act->execution = $codage->_acte_execution;
    $_act->executant_id = $codage->praticien_id;

    if ($_act->_class == 'CActeLPP') {
      $_act->date = $codage->date;
    }

    $_act->store();
  }
}

$codage->updateFormFields();
$codage->loadRefsActes();
$codage->loadExtCodesCCAM();
$codage->loadRefsCodagesCCAM();
if (array_key_exists($chir_id, $codage->_ref_codages_ccam)) {
  $codages_ccam = $codage->_ref_codages_ccam[$chir_id];
}
else {
  $codages_ccam = array();
}

foreach ($codages_ccam as $_codage_ccam) {
  $_codage_ccam->loadPraticien()->loadRefFunction();
  $_codage_ccam->_ref_praticien->isAnesth();
  $_codage_ccam->loadActesCCAM();
  $_codage_ccam->getTarifTotal();
  $_codage_ccam->checkRules();

  foreach ($_codage_ccam->_ref_actes_ccam as $_acte) {
    $_acte->getTarif();
  }

  // Chargement du codable et des actes possibles
  $_codage_ccam->loadCodable();
  $codable = $_codage_ccam->_ref_codable;
  $praticien = $_codage_ccam->_ref_praticien;
}

if (CModule::getActive('lpp') && CAppUI::gconf('lpp General cotation_lpp')) {
  $codage->loadRefsActesLPP();

  foreach ($codage->_ref_actes_lpp as $_acte) {
    $_acte->loadRefExecutant();
    $_acte->_ref_executant->loadRefFunction();
  }

  $acte_lpp = CActeLPP::createFor($codage);
}

$user = $codage->loadRefPraticien();
$user->loadRefFunction();
$user->isAnesth();
$user->isPraticien();
$user->isProfessionnelDeSante();

$codage->getActeExecution();
$codage->loadPossibleActes($codage->praticien_id);
$codage->loadRefPatient();
$codage->canDo();

// Initialisation d'un acte NGAP
$acte_ngap = CActeNGAP::createEmptyFor($codage);

$users = array($user);
$anesths = array();
if ($user->_is_anesth) {
  $anesths[] =  $user;
}

$smarty = new CSmartyDP();
$smarty->assign('subject', $codage);
$smarty->assign('user', $user);
$smarty->assign('acte_ngap', $acte_ngap);
if (CModule::getActive('lpp') && CAppUI::gconf('lpp General cotation_lpp')) {
  $smarty->assign('acte_lpp', $acte_lpp);
}
$smarty->assign('listPrats', $users);
$smarty->assign('listChirs', $users);
$smarty->assign('listAnesths', $anesths);
$smarty->display('inc_edit_actes_tarif.tpl');
