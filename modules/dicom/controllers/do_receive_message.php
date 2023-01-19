<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Core\CApp;
use Ox\Core\CValue;
use Ox\Interop\Dicom\CDicomSender;
use Ox\Interop\Dicom\CDicomSession;

$addr    = CValue::post("client_addr");
$port    = CValue::post("client_port");
$message = base64_decode(CValue::post("message"));

$session = getSession($addr, $port);

if ($session) {
  $session->updateFormFields();
  $session->loadRefActor();
  
  $pdus = cutPacket($message);

  foreach ($pdus as $key => $pdu) {
    $event = "";
    $datas = "";
    if ($pdu == "TCP_Open") {
      $event = $pdu;
      $datas = null;
    }
    elseif ($pdu == "TCP_Closed") {
      CApp::rip();
    }
    else {
      $datas = $pdu;
      $type = unpack("H*", substr($pdu, 0, 1));
      $event = getEventName($type[1]);
    }
    $ack = $session->handleEvent($event, $pdu);
    $session->store();
    
    if ($ack) {
      echo base64_encode($ack);
    }
    else {
      echo " ";
    }
  } 
}
CApp::rip();

/**
 * Return the session corresponding to the ip adress of the sender
 * 
 * @param string  $addr The ip adress
 * 
 * @param integer $port The port
 * 
 * @return CDicomSession
 */
function getSession($addr, $port) {
  $dicom_sender = new CDicomSender();
  $dicom_sender->actif = '1';
  /** @var CDicomSender[] $dicom_senders */
  $dicom_senders = $dicom_sender->loadMatchingList();
  $dicom_sender = null;
  foreach ($dicom_senders as $_sender) {
    $source = $_sender->getFirstExchangesSources();
    if ($source->host == $addr /*&& $_sender->_ref_exchanges_sources[0]->port == $port*/) {
      $dicom_sender = $_sender;
      break;
    }
  }
  
  if (!$dicom_sender->_id) {
    return false;
  }
  
  $session = new CDicomSession();

  $where = array();
  $where["sender_id"] = " = '$dicom_sender->_id'";
  $where["status"]    = " IS NULL";
  $where["state"]     = " != 'Sta13'";
  $where["end_date"]  = " IS NULL";

  $session->loadObject($where);
  
  if (!$session->_id) {
    $session = new CDicomSession($dicom_sender);
  }
  return $session;
}

/**
 * Return the event name, depends on the PDU type
 * 
 * @param string $type The PDU type
 * 
 * @return string
 */
function getEventName($type) {
  switch ($type) {
    case "01" :
      $event = "AAssociateRQ_Received";
      break;
    case "02" :
      $event = "AAssociateAC_Received";
      break;
    case "03" :
      $event = "AAssociateRJ_Received";
      break;
    case "04" :
      $event = "PDataTF_Received";
      break;
    case "05" :
      $event = "AReleaseRQ_Received";
      break;
    case "06" :
      $event = "AReleaseRP_Received";
      break;
    case "07" :
      $event = "AAbort_Received";
      break;
    default :
      $event = "InvalidPDU";
      break; 
  }
  return $event;
}

/**
 * Check if the message contains multiple PDUs
 * 
 * @param string $message The message
 * 
 * @return array An array of pdu
 */
function cutPacket($message) {
  $messages = array();
  
  $packet_size = strlen($message);
  $pos = 0;
  while ($pos < $packet_size) {
    $length = unpack("N", substr($message, $pos + 2, 4));
    $length = $length[1] + 6;
    $messages[] = substr($message, $pos, $length);
    $pos += $length;
  }
  
  return $messages;
}
