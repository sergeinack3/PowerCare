<?php
/**
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */


use Ox\Core\CAppUI;
use Ox\Core\CMbArray;
use Ox\Interop\Dmp\CExchangeDMP;
use Ox\Interop\Eai\CMessageSupported;
use Ox\Interop\Eai\CSyslogExchange;
use Ox\Interop\Eai\CSyslogITI;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\Events\PRPA\CHL7v3EventPRPA;
use Ox\Interop\Hl7\Events\SVS\CHL7v3EventSVS;
use Ox\Interop\Hl7\Events\XDSb\CHL7v3EventXDSb;
use Ox\Interop\Hprimxml\CEchangeHprim;
use Ox\Interop\Hprimxml\CHPrimXMLEvenements;
use Ox\Interop\Phast\CExchangePhast;
use Ox\Interop\Phast\CPhastEvenementsPN13;

$message_supported     = new CMessageSupported();
$where[]               = "profil IS NULL OR profil = ''";
$messages_supported    = $message_supported->loadList($where);

foreach ($messages_supported as $_message_supported) {

  $message = $_message_supported->message;

  if (preg_match("/CHPrimXML/", $message)) {
    if (class_exists($message)) {
      $class = new $message;
      if ($class instanceof CHPrimXMLEvenements) {
        $_message_supported->profil = CEchangeHprim::$messages[$class->type];
        $_message_supported->store();
        CAppUI::stepAjax("CMessageSupported-msg-modify");
      }
    }
  }

  // HprimSante - H' 2.1
  if (preg_match("/CHPrimSante/", $message) || preg_match("/CHPrim21/", $message)) {
    $profil = "C" . substr($_message_supported->message, -4, 4);
    $profil = substr($profil, 0, 4);
    $_message_supported->profil = $profil;
    $_message_supported->store();
    CAppUI::stepAjax("CMessageSupported-msg-modify");

  }

  // HL7V2 :
  if (preg_match("/CHL7Event/", $message)) {
    // Message FRA
    $base_classname  = $_message_supported->message;
    $begin_classname = substr($_message_supported->message, 0, 4);
    $end_classname   = substr($_message_supported->message, 4);

    $class = $begin_classname . "v2" . $end_classname;
    $object = new $class;
    $_message_supported->profil = CMbArray::get(CExchangeHL7v2::$messages, $object->profil);
    $_message_supported->store();
    CAppUI::stepAjax("CMessageSupported-msg-modify");
  }

  // HL7v3 :
  if (preg_match("/CHL7v3/", $message)) {

    /** @var CHL7v3EventPRPA|CHL7v3EventXDSb|CHL7v3EventSVS $object */
    $object = null;

    // Profil SVS
    if (preg_match("/CHL7v3EventSVS/", $message)) {
      $object = new CHL7v3EventSVS();
    }

    // Profil XDS
    if (preg_match("/CHL7v3EventXDSb/", $message)) {
      $object = new CHL7v3EventXDSb();
    }

    // Profil PRPA
    if (preg_match("/CHL7v3EventPRPA/", $message)) {
      $object = new CHL7v3EventPRPA();
    }

    $_message_supported->profil = "C" . $object->event_type;
    $_message_supported->store();
    CAppUI::stepAjax("CMessageSupported-msg-modify");
  }

  // Phast
  if (preg_match("/CPhast/", $message)) {
    if (class_exists($message)) {
      $class = new $message;
      if ($class instanceof CPhastEvenementsPN13) {
        $_message_supported->profil = CExchangePhast::$messages[$class->type];
        $_message_supported->store();
        CAppUI::stepAjax("CMessageSupported-msg-modify");
      }
    }
  }

  // Syslog
  if (preg_match("/CSyslog/", $message)) {
    $class = new CSyslogITI();
    $_message_supported->profil = CSyslogExchange::$messages[$class->type];
    $_message_supported->store();
    CAppUI::stepAjax("CMessageSupported-msg-modify");
  }

  // FHIR
  if (preg_match("/CFHIR/", $message)) {
    $profil = "";

    if (preg_match("/CFHIRInteraction/", $message)) {
      $profil = "CPDQm";
    }

    if (preg_match("/CFHIROperationIhePix/", $message)) {
      $profil = "CPIXm";
    }

    $_message_supported->profil = $profil;
    $_message_supported->store();
    CAppUI::stepAjax("CMessageSupported-msg-modify");
  }

  // Dicom
  if (preg_match("/CDicom/", $message)) {
    $profil = "";

    if (preg_match("/Find/", $message)) {
      $profil = "CFind";
    }

    if (preg_match("/Echo/", $message)) {
      $profil = "CEcho";
    }

    $_message_supported->profil = $profil;
    $_message_supported->store();
    CAppUI::stepAjax("CMessageSupported-msg-modify");
  }

  // DMP
  if (preg_match("/CDMP/", $message)) {
    if (class_exists($message)) {
      $class = new $message;

      $_message_supported->profil = CExchangeDMP::$messages[$class->evenement_type];
      $_message_supported->store();
      CAppUI::stepAjax("CMessageSupported-msg-modify");
    }
  }
}
