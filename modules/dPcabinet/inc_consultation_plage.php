<?php
/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\AppFine\Client\CAppFineClient;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\Module\CModule;
use Ox\Core\CSmartyDP;
use Ox\Core\CStoredObject;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\SmsProviders\CLotSms;
use Ox\Mediboard\Tel3333\C3333TelTools;

CCanDo::checkRead();

// L'utilisateur est-il praticien ?
$chir = null;
$mediuser = CMediusers::get();
if ($mediuser->isPraticien()) {
  $chir = $mediuser->createUser();
}

// Praticien selectionné
$chirSel = CView::get("chirSel", "ref class|CMediusers" . ($chir ? " default|$chir->user_id" : null), true);

// Type de vue
$show_payees     = CView::get("show_payees", "bool default|1", true);
$show_annulees   = CView::get("show_annulees", "bool default|0", true);
$plageconsult_id = CView::get("plageconsult_id", "ref class|CPlageconsult", true);

CView::checkin();

$user = CMediusers::get($chirSel);
$see_notification = CModule::getActive("smsProviders") && $chirSel && count(CLotSms::loadForUser($user, false)) ? 1 : 0;

// Plage de consultation selectionnée
$plageSel = new CPlageconsult();
if (($plageconsult_id === null) && $chirSel && $is_in_period) {
  $nowTime = CMbDT::time();
  $where = array(
    "chir_id = '$chirSel' OR remplacant_id = '$chirSel' OR pour_compte_id = '$chirSel'",
    "date"    => "= '$today'",
    "debut"   => "<= '$nowTime'",
    "fin"     => ">= '$nowTime'"
  );
  $plageSel->loadObject($where);
}
if (!$plageSel->_id) {
  $plageSel->load($plageconsult_id);
}

$plageSel->loadRefChir();
$plageSel->loadRefRemplacant();
$plageSel->loadRefPourCompte();
$plageSel->loadRefsNotes();
$plageSel->loadRefsBack($show_annulees, true, $show_payees);

if ($plageSel->_affected && count($plageSel->_ref_consultations)) {
  $firstconsult = reset($plageSel->_ref_consultations);
  $_firstconsult_time = substr($firstconsult->heure, 0, 5);
  $lastconsult = end($plageSel->_ref_consultations);
  $_lastconsult_time  = substr($lastconsult->heure, 0, 5);
}

$consults = $plageSel->_ref_consultations;
CStoredObject::massLoadFwdRef($consults, "sejour_id");
$patients = CStoredObject::massLoadFwdRef($consults, "patient_id");
CStoredObject::massLoadBackRefs($patients, "notes");
CStoredObject::massLoadFwdRef($consults, "categorie_id");
$dossiers_anesth = CStoredObject::massLoadBackRefs($consults, "consult_anesth");
if ($see_notification) {
  CStoredObject::massLoadBackRefs($consults, "context_notifications");
}
CMbObject::countAlertDocs($consults);

// Il faut aussi compter les documents paramétrés en alerte sur le semainier pour les dossiers d'anesthésie
CMbObject::countAlertDocs($dossiers_anesth);
foreach ($consults as $_consult) {
  foreach ($_consult->_back["consult_anesth"] as $_consult_anesth) {
    $_consult->_alert_docs += $_consult_anesth->_alert_docs;
  }
}

$m3333tel_active = CModule::getActive("3333tel");

// Détails sur les consultation affichées
foreach ($plageSel->_ref_consultations as $consultation) {
  $consultation->_ref_plageconsult = $plageSel;
  $consultation->loadRefSejour();
  $consultation->loadRefPatient()->loadRefsNotes();
  $consultation->loadRefCategorie();
  $consultation->loadRefConsultAnesth();
  $consultation->canDo();
  if ($see_notification) {
    $consultation->loadRefNotification();
  }
  $consultation->_view = "Consult. de ".$consultation->_ref_patient->_view;
  $consultation->_view .= " (".CMbDT::format($plageSel->date, CAppUI::conf("date")).")";
  //check 3333tel
  if ($m3333tel_active) {
    C3333TelTools::checkConsults($consultation, $plageSel->_ref_chir->function_id);
  }
  $consultation->checkDHE($plageSel->date);

  if (CModule::getActive("appFineClient")) {
    CAppFineClient::loadIdex($consultation, CGroups::loadCurrent()->_id);
    CAppFineClient::loadIdex($consultation->_ref_patient, CGroups::loadCurrent()->_id);
    $consultation->_ref_patient->loadRefStatusPatientUser();
  }
}

/* The template inc_consultations_lines iterate on the _items property, instead of the ref_consultations, to include patient events */
$plageSel->_items = $plageSel->_ref_consultations;

if ($plageSel->chir_id != $chirSel && $plageSel->remplacant_id != $chirSel &&  $plageSel->pour_compte_id != $chirSel) {
  $plageSel = new CPlageconsult();
}

// Création du template
$smarty = new CSmartyDP();

$smarty->assign("plageSel"     , $plageSel);
$smarty->assign("chirSel"      , $chirSel);
$smarty->assign("show_payees"  , $show_payees);
$smarty->assign("show_annulees", $show_annulees);
$smarty->assign("mediuser"     , $mediuser);

$smarty->display("inc_consultations.tpl");
