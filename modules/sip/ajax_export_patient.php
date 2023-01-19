<?php
/**
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CValue;
use Ox\Interop\Eai\CInteropActorFactory;
use Ox\Interop\Hprimxml\CHPrimXMLEnregistrementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;

CCanDo::checkAdmin();

if (!CAppUI::conf("sip export_dest")) {
  CAppUI::stepAjax("Aucun destinataire de défini pour l'export.", UI_MSG_ERROR);
}

// Si pas de tag patient
if (!CAppUI::conf("dPpatients CPatient tag_ipp")) {
  CAppUI::stepAjax("Aucun tag patient de défini.", UI_MSG_ERROR);
}

// Filtre sur les enregistrements
$patient = new CPatient();
$action = CValue::get("action", "start");

// Tous les départs possibles
$idMins = array(
  "start"    => "000000",
  "continue" => CValue::getOrSession("idContinue"),
  "retry"    => CValue::getOrSession("idRetry"),
);

$idMin = CValue::first(@$idMins[$action], "000000");
CValue::setSession("idRetry", $idMin);

// Requêtes
$where = array();
$where[$patient->_spec->key] = "> '$idMin'";

$sip_config = CAppUI::conf("sip");

// Bornes
if ($export_id_min = $sip_config["export_id_min"]) {
  $where[] = $patient->_spec->key." >= '$export_id_min'";
}
if ($export_id_max = $sip_config["export_id_max"]) {
  $where[] = $patient->_spec->key." <= '$export_id_max'";
}

// Comptage
$count = $patient->countList($where);
$max = $sip_config["export_segment"];
$max = min($max, $count);
CAppUI::stepAjax("Export de $max sur $count objets de type 'CPatient' à partir de l'ID '$idMin'", UI_MSG_OK);

// Time limit
$seconds = max($max / 20, 120);
CAppUI::stepAjax("Limite de temps du script positionné à '$seconds' secondes", UI_MSG_OK);
CApp::setTimeLimit($seconds);

// Export réel
$errors = 0;
$patients = $patient->loadList($where, $patient->_spec->key, "0, $max");

$echange = 0;
foreach ($patients as $patient) {
  $patient->loadIPP();
  $patient->loadRefsSejours();
  $patient->_ref_last_log->type = "create";
  
  $receiver =  (new CInteropActorFactory())->receiver()->makeHprimXML();
  $receiver->load(CAppUI::conf("sip export_dest"));
  $receiver->loadConfigValues();

  if (!$patient->_IPP) {
    $IPP = new CIdSante400();
    //Paramétrage de l'id 400
    $IPP->object_class = "CPatient";
    $IPP->object_id = $patient->_id;
    $IPP->tag = $receiver->_tag_patient;
    $IPP->loadMatchingObject();

    $patient->_IPP = $IPP->id400;
  }

  if ((CAppUI::conf("sip pat_no_ipp") && $patient->_IPP  && ($patient->_IPP != "-")) || 
      (!$receiver->_configs["send_all_patients"] && empty($patient->_ref_sejours))) {
    continue;
  }

  $dom = new CHPrimXMLEnregistrementPatient();
  $dom->_ref_receiver = $receiver;
  $receiver->sendEvenementPatient($dom, $patient);
  
  if (!$dom->_ref_echange_hprim->message_valide) {
    $errors++;
    trigger_error("Création de l'événement patient impossible.", E_USER_WARNING);
    CAppUI::stepAjax("Import de '$patient->_view' échoué", UI_MSG_WARNING);
  }
  $echange++;
}

// Enregistrement du dernier identifiant dans la session
if (@$patient->_id) {
  CValue::setSession("idContinue", $patient->_id);
  CAppUI::stepAjax("Dernier ID traité : '$patient->_id'", UI_MSG_OK);
  CAppUI::stepAjax("$echange de créés", UI_MSG_OK);
}

CAppUI::stepAjax("Import terminé avec  '$errors' erreurs", $errors ? UI_MSG_WARNING : UI_MSG_OK);

