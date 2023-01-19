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
use Ox\Core\CValue;
use Ox\Interop\Eai\CEAIDispatcher;
use Ox\Interop\Eai\Resilience\ClientContext;
use Ox\Interop\Hl7\CExchangeHL7v2;
use Ox\Interop\Hl7\CHL7v2Exception;
use Ox\Interop\Hl7\CHL7v2TableEntry;
use Ox\Interop\Hl7\CSenderMLLP;
use Ox\Interop\Hl7\CSourceMLLP;

/**
 * Receive MLLP
 */
CCanDo::checkEdit();

$client_addr = CValue::post("client_addr");
$server_port = CValue::post("port");
$message = stripslashes(CValue::post("message"));

$guid_prefix = "CSenderMLLP-";
$where = [];

$source_mllp = new CSourceMLLP();
$ds = $source_mllp->getDS();

// Source
$where["source_mllp.host"] = $ds->prepareLike("%" . $client_addr . "%");
$where["source_mllp.active"] = " = '1'";
$where["source_mllp.name"] = "LIKE '$guid_prefix%'";
$where["source_mllp.role"] = " = '" . CAppUI::conf("instance_role") . "'";

if ($server_port) {
    $where["source_mllp.port"] = " = '$server_port'";
}

// Sender
$where["sender_mllp.actif"] = " = '1'";
$where["sender_mllp.role"]  = " = '" . CAppUI::conf("instance_role") . "'";

$ljoin                = [];
$ljoin["sender_mllp"] = "sender_mllp.sender_mllp_id = SUBSTR(source_mllp.name, " . (strlen(
            $guid_prefix
        ) + 1) . ")"; // 'CSenderMLLP-XX'

$source_mllp->loadObject($where, null, null, $ljoin);
$source_mllp->loggable = 1;
$client_mllp = $source_mllp->getClient();

$context = new ClientContext($client_mllp, $source_mllp);
$context->setRequest($message);
$context->setArguments(['server' => true, 'function_name' => 'receive']);
$source_mllp->_dispatcher->dispatch($context, $client_mllp::EVENT_BEFORE_REQUEST);
if (!$source_mllp->_id) {
    /*
    $message          = new CHL7v2Message();
    $message->version = "2.5";
    $message->name    = "ACK";

    // Message Header
    $MSH = CHL7v2Segment::create("MSH", $message);
    $data = array();

    // MSH-1: Field Separator (ST)
    $data[] = $message->fieldSeparator;

    // MSH-2: Encoding Characters (ST)
    $data[] = substr($message->getEncodingCharacters(), 1);

    // MSH-3: Sending Application (HD) (optional)
    $data[] = CAppUI::conf("hl7 sending_application");

    // MSH-4: Sending Facility (HD) (optional)
    $data[] = CAppUI::conf("hl7 sending_facility");

    // MSH-5: Receiving Application (HD) (optional)
    $data[] = null;

    // MSH-6: Receiving Facility (HD) (optional)
    $data[] = null;

    // MSH-7: Date/Time Of Message (TS)
    $data[] = CMbDT::dateTime();

    // MSH-8: Security (ST) (optional)
    $data[] = null;

    // MSH-9: Message Type (MSG)
    $data[] = array(array(
      "ACK", "A12", "ACK"
    ));

    // MSH-10: Message Control ID (ST)
    $data[] = null;

    // MSH-11: Processing ID (PT)
    $data[] = (CAppUI::conf("instance_role") == "prod") ? "P" : "D";

    // MSH-12: Version ID (VID)
    $data[] = CHL7v2::prepareHL7Version("2.5");

    // MSH-13: Sequence Number (NM) (optional)
    $data[] = null;

    // MSH-14: Continuation Pointer (ST) (optional)
    $data[] = null;

    // MSH-15: Accept Acknowledgment Type (ID) (optional)
    $data[] = null;

    // MSH-16: Application Acknowledgment Type (ID) (optional)
    $data[] = null;

    // MSH-17: Country Code (ID) (optional)
    $data[] = CHL7v2TableEntry::mapTo("399", "250");

    // MSH-18: Character Set (ID) (optional repeating)
    $data[] = CHL7v2TableEntry::mapTo("211", CApp::$encoding);

    // MSH-19: Principal Language Of Message (CE) (optional)
    $data[] = array(
      "FR"
    );

    $MSH->fill($data);

    $message->appendChild($MSH);

    // Error
    $error = new CHL7v2Error();
    $error->message = "booh";

    $ERR = CHL7v2Segment::create("ERR", $message);
    $ERR->error = $error;
    $ERR->build($error);

    $message->appendChild($ERR);*/

    $now = CMbDT::format(null, "%Y%m%d%H%M%S");
    $ACK = "MSH|^~\&|" . CAppUI::conf("hl7 CHL7 sending_application", "global") . "|" . CAppUI::conf(
            "hl7 CHL7 sending_facility",
            "global"
        ) .
        "|||$now||ACK|$now|P|2.5||||||" . CHL7v2TableEntry::mapTo("211", CApp::$encoding);
    $ACK .= "\r" . "MSA|AR|$now";
    $ACK .= "\r" . "ERR||0^0|207|E|E200^Acteur inconnu|||||||";

    $context->setResponse($ACK);
    $source_mllp->_dispatcher->dispatch($context, $client_mllp::EVENT_AFTER_REQUEST);
    ob_clean();
    echo $ACK;
    CApp::rip();
}

/** @var CSenderMLLP $sender_mllp */
$sender_mllp = CMbObject::loadFromGuid($source_mllp->name);

if (!$source_mllp->active || !$sender_mllp->actif) {
    $now = CMbDT::format(null, "%Y%m%d%H%M%S");
    $ACK = "MSH|^~\&|" . CAppUI::conf("hl7 CHL7 sending_application", "global") . "|" . CAppUI::conf(
            "hl7 CHL7 sending_facility",
            "global"
        ) .
        "|||$now||ACK|$now|P|2.5||||||" . CHL7v2TableEntry::mapTo("211", CApp::$encoding);
    $ACK .= "\r" . "MSA|AR|$now";
    $ACK .= "\r" . "ERR||0^0|207|E|E200^Source non active|||||||";

    $context->setResponse($ACK);
    $source_mllp->_dispatcher->dispatch($context, $client_mllp::EVENT_AFTER_REQUEST);
    ob_clean();
    echo $ACK;
    CApp::rip();
}

// Dispatch EAI
try {
    $ack = CEAIDispatcher::dispatch($message, $sender_mllp);
} catch (CHL7v2Exception $e) {
    $sender_mllp->getConfigs(new CExchangeHL7v2());
    $configs = $sender_mllp->_configs;

    $now         = CMbDT::format(null, "%Y%m%d%H%M%S");
    $sending_app = CAppUI::conf("hl7 CHL7 sending_application", "global");
    $sending_fac = CAppUI::conf("hl7 CHL7 sending_facility", "global");

    $recv_app = isset($configs["receiving_application"]) ? $configs["receiving_application"] : $sender_mllp->nom;
    $recv_fac = isset($configs["receiving_facility"]) ? $configs["receiving_facility"] : $sender_mllp->nom;

    $ack = "MSH|^~\&|$sending_app|$sending_fac|$recv_app|$recv_fac|$now||ACK^R01^ACK|$now|P|2.6||||||" .
        CHL7v2TableEntry::mapTo("211", CApp::$encoding);
    $ack .= "\r\n" . "MSA|CR|$now";
    $ack .= "\r\n" . "ERR||0^0|207|E|E200^" . $e->getMessage() . "|||||||";
}

$context->setResponse($ACK);
$source_mllp->_dispatcher->dispatch($context, $client_mllp::EVENT_AFTER_REQUEST);
ob_clean();

echo $ack;

CApp::rip();
