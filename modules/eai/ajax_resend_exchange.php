<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CSQLDataSource;
use Ox\Core\CValue;
use Ox\Core\CView;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Hprimxml\CDestinataireHprim;
use Ox\Interop\Ihe\CITI31DelegatedHandler;
use Ox\Mediboard\Hospi\CMovement;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Resend message
 */
CCanDo::checkAdmin();

$receiver_guid     = CView::get("receiver_guid"    , "guid class|CInteropReceiver");
$list_nda          = CView::get("list_nda"         , "str");
$list_ipp          = CView::get("list_ipp"         , "str");
$sejour_type       = CView::get("sejour_type"      , "str");
$charge_price_id   = CView::get("charge_price_id"  , "str");
$mediuser_id       = CView::get("mediuser_id"      , "ref class|");
$action            = CView::get("action"           , "str");
$id_start          = CView::get("id_start"         , "num");
$idContinue        = CView::get("idContinue"       , "num", true);
$idRetry           = CView::get("idRetry"          , "num", true);
$date_min          = CView::get('date_min'         , array('dateTime', 'default' => CMbDT::dateTime("-7 day")));
$date_max          = CView::get('date_max'         , array('dateTime', 'default' => CMbDT::dateTime("+1 day")));
$count             = CView::get("count"            , "num default|30");
$only_pread        = CView::get("only_pread"       , "bool");
$without_exchanges = CView::get("without_exchanges", "bool");
$movement_type     = CView::get("movement_type"    , "str");

if (!$receiver_guid) {
  CAppUI::stepAjax("CInteropReceiver.none", UI_MSG_ERROR);
}

$receiver = CMbObject::loadFromGuid($receiver_guid);
$receiver->loadConfigValues();

// On rejoue pour une liste de NDA
if ($list_nda) {
  $ndas = explode("|", $list_nda);
  
  $sejours = array();
  foreach ($ndas as $_nda) {
    $sejour = new CSejour();
    $sejour->loadFromNDA($_nda);

    if ($sejour->_id) {
      $sejours[] = $sejour;
    }
  }
}
else {
  // Filtre sur les enregistrements
  $sejour = new CSejour();

  // Tous les départs possibles
  $idMins = array(
    "start"    => $id_start,
    "continue" => $idContinue,
    "retry"    => $idRetry,
  );
  
  $idMin = CValue::first(@$idMins[$action], "000000");
  CView::setSession("idRetry", $idMin);
  
  // Requêtes
  $where = array();
  $where[$sejour->_spec->key] = "> '$idMin'";
  $where['annule']            = " = '0'";

  if ($sejour_type) {
    $where['type'] = " = '$sejour_type'";
  }
  if ($mediuser_id) {
    $where['praticien_id'] = " = '$mediuser_id'";
  }
  if ($charge_price_id) {
    $where['charge_id'] = " = '$charge_price_id'";
  }
  if ($list_ipp) {
    $ipps = explode("|", $list_ipp);

    $patient = new CPatient();
    $ljoin_patient = "id_sante400 ON id_sante400.object_id = patients.patient_id AND id_sante400.object_class = 'CPatient'";
    $where_patient['id_sante400.id400'] = CSQLDataSource::prepareIn($ipps);

    $ids_patient = $patient->loadIds($where_patient, null, null,  null, $ljoin_patient);
    $where['patient_id'] = CSQLDataSource::prepareIn($ids_patient);
  }
  
  // Bornes
  $where['entree'] = " BETWEEN '$date_min' AND '$date_max'";

  // Comptage
  $count_sejours = $sejour->countList($where);
  $max           = min($count, $count_sejours);
  CAppUI::stepAjax("Export de $max sur $count_sejours objets de type 'CSejour' à partir de l'ID '$idMin'", UI_MSG_OK);
  
  // Time limit
  $seconds = max($max / 20, 120);
  CAppUI::stepAjax("Limite de temps du script positionné à '$seconds' secondes", UI_MSG_OK);
  CApp::setTimeLimit($seconds);
  
  // Export réel
  $sejours = $sejour->loadList($where, $sejour->_spec->key, "0, $max");
}

$errors   = 0;
$exchange = 0;
foreach ($sejours as $_sejour) {
  $_sejour->loadRefPraticien();
  $_sejour->loadRefPatient();
  $_sejour->loadNDA($receiver->group_id);
  $_sejour->loadLastLog();
  
  $_sejour->_ref_last_log->type = "create";
  
  if ($only_pread && ($_sejour->_etat != "preadmission")) {
    continue;
  }
      
  if ($without_exchanges && ($_sejour->countExchanges() > 0)) {
    continue;
  }
  
  if ($receiver instanceof CDestinataireHprim) {
    CAppUI::stepAjax("Le traitement pour ce destinataire n'est pas pris en charge", UI_MSG_ERROR); 
  }
  
  if ($receiver instanceof CReceiverHL7v2) {
    $receiver->getInternationalizationCode("ITI31");  
    $_sejour->_receiver = $receiver;

    $iti31 = new CITI31DelegatedHandler();
    
    $movement                = new CMovement();
    $movement->sejour_id     = $_sejour->_id;
    $movement->movement_type = $movement_type;
    $movements = $movement->loadMatchingList();
    if (empty($movements)) {
    }

    foreach ($movements as $_movement) {
      $code = $_movement->original_trigger_code;

      if (!$iti31->isMessageSupported("ADT", $code, $receiver)) {
        $errors++;
        CAppUI::stepAjax("Le destinataire ne prend pas en charge cet événement", UI_MSG_WARNING);
      }

      $_sejour->_ref_hl7_movement = $_movement;
      
      try {
        // Envoi de l'événement
        $iti31->sendITI("PAM", "ITI31", "ADT", $code, $_sejour);
        $exchange++;
      }
      catch (Exception $e) {
        $errors++;
        CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
      }
    }
  }
}
    
// Enregistrement du dernier identifiant dans la session
if (@$_sejour->_id) {
  CView::setSession("idContinue", $_sejour->_id);
  CAppUI::stepAjax("Dernier ID traité : '$_sejour->_id'", UI_MSG_OK);
  if (!$errors) {
    CAppUI::stepAjax("$exchange de créés", UI_MSG_OK);
  }
}

CView::checkin();

CAppUI::stepAjax("Import terminé avec  '$errors' erreurs", $errors ? UI_MSG_WARNING : UI_MSG_OK);


