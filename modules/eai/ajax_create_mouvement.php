<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CView;
use Ox\Interop\Hl7\CReceiverHL7v2;
use Ox\Interop\Ihe\CITI31DelegatedHandler;
use Ox\Mediboard\PlanningOp\CSejour;

/**
 * Create mouvement
 */
CCanDo::checkAdmin();

$limit         = CView::get("count"        , "num");
$receiver_guid = CView::get("receiver_guid", "guid class|CInteropReceiver");
$date_min      = CView::get('date_minimum' , array('dateTime', 'default' => CMbDT::dateTime("-7 day")));
$date_max      = CView::get('date_maximum' , array('dateTime', 'default' => CMbDT::dateTime("+1 day")));
$movement      = CView::get("movement"     , "str");
CView::checkin();

if (!$receiver_guid) {
  CAppUI::stepAjax("CInteropReceiver.none", UI_MSG_ERROR);
}

$receiver = CMbObject::loadFromGuid($receiver_guid);
$receiver->loadConfigValues();

if (!$receiver instanceof CReceiverHL7v2) {
  CAppUI::stepAjax("CInteropReceiver-msg-Not supported", UI_MSG_ERROR);
}

$ljoin["movement"] = "sejour.sejour_id = movement.sejour_id";
$where = array(
  "sejour.entree" => " BETWEEN '$date_min' AND '$date_max' ",
  "movement.sejour_id" => " = sejour.sejour_id IS NULL",
);

$sejour = new CSejour();
/** @var CSejour[] $sejours */
$sejours = $sejour->loadList($where, null, $limit, null, $ljoin);

$errors   = 0;
$exchange = 0;
foreach ($sejours as $_sejour) {
  $receiver->getInternationalizationCode("ITI31");
  $_sejour->_receiver = $receiver;
  $_sejour->loadRefPatient(1);

  $first_affectation = $_sejour->loadRefFirstAffectation();

  $code = null;
  if ($_sejour->_etat == "preadmission") {
    $code = "A05";
  }
  if ($_sejour->_etat == "encours") {
    $code = in_array($_sejour->type, CITI31DelegatedHandler::getOutpatient($_sejour->loadRefEtablissement())) ? "A04" : "A01";
  }
  if ($_sejour->_etat == "cloture") {
    $code = "A03";
  }

  $code = $movement ? $movement : $code;

  if (!$code) {
    continue;
  }

  $iti31 = new CITI31DelegatedHandler();
  if (!$iti31->isMessageSupported("ADT", $code, $receiver)) {
    $errors++;
    CAppUI::stepAjax("Le destinataire ne prend pas en charge cet événement", UI_MSG_WARNING);
  }

  try {
    $iti31->createMovement($code, $_sejour, $first_affectation);
    $iti31->sendITI("PAM", "ITI31", "ADT", $code, $_sejour);
    $exchange++;
  }
  catch (Exception $e) {
    $errors++;
    CAppUI::stepAjax($e->getMessage(), UI_MSG_WARNING);
  }
}

CAppUI::stepAjax("$exchange échanges de créés", UI_MSG_OK);
CAppUI::stepAjax("Import terminé avec  '$errors' erreurs", $errors ? UI_MSG_WARNING : UI_MSG_OK);
