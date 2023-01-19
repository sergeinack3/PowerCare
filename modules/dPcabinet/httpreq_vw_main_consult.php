<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CValue;
use Ox\Mediboard\Admin\CAccessMedicalData;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Mediusers\CMediusers;

CCanDo::checkEdit();

// Utilisateur sélectionné ou utilisateur courant
$prat_id = CValue::getOrSession("chirSel", 0);

$userSel = CMediusers::get($prat_id);
$userSel->loadRefs();
$canUserSel = $userSel->canDo();

// Vérification des droits sur les praticiens
$listChir = CConsultation::loadPraticiens(PERM_EDIT);

if (!$userSel->isPraticien()) {
  CAppUI::setMsg("Vous devez selectionner un praticien", UI_MSG_ALERT);
  CAppUI::redirect("m=dPcabinet&tab=0");
}

$canUserSel->needsEdit();

$selConsult = CValue::getOrSession("selConsult");

// Consultation courante
$consult = new CConsultation();
$consult->_ref_chir = $userSel;
if ($selConsult) {
  $consult = CConsultation::findOrFail($selConsult);

  CAccessMedicalData::logAccess($consult);

  $canConsult = $consult->canDo();
  $canConsult->needsEdit();
  
  $consult->loadRefsFwd();
  $userSel->load($consult->_ref_plageconsult->chir_id);
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("consult", $consult);
$smarty->assign("userSel", $userSel);
$smarty->display("inc_main_consultform.tpl");
