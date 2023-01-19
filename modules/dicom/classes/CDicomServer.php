<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom;

use Ox\Core\CSocketBasedServer;

/**
 * A Dicom server listening with a socket of a port
 */
class CDicomServer extends CSocketBasedServer {
  
  /**
   * The module 
   * 
   * @var string
   */
  protected $module = "dicom";
  
  /**
   * The controller who will receive the messages
   * 
   * @var string
   */
  protected $controller = "do_receive_message";
  
  /**
   * Check if the message is complete
   * 
   * @param string $message The message
   * 
   * @return boolean
   */
  function isMessageFull($message) {
    $type = unpack("C", substr($message, 0, 1));
    
    if (!$this->isPDUTypeValid($type[1])) {
      return false;
    }
    
    $length = unpack("N", substr($message, 2, 4));
    $length = $length[1] + 6;
    if ($length == strlen($message)) {
      return true;
    }
    else {
      if ($length > strlen($message)) {
        return false;
      }
      
      $nextPDU = substr($message, $length);
      return $this->isMessageFull($nextPDU);
    }
  }

  /**
   * @param $type
   *
   * @return bool
   */
  function isPDUTypeValid($type) {
    switch ($type) {
      case 0x01 :
      case 0x02 :
      case 0x03 :
      case 0x04 :
      case 0x05 :
      case 0x06 :
      case 0x07 :
        return true;
      default :
        return false;
    }
  }
  
  /**
   * The open connection callback
   * 
   * @param integer $id   The client's ID
   * @param string  $addr The client's IP address
   * @param integer $port The client's port
   * 
   * @return boolean true
   */
  function onOpen($id, $addr, $port = null) {
    $post = array(
      "m"       => $this->module,
      "dosql"   => $this->controller,
      "port"    => $this->port,
      "message" => base64_encode("TCP_Open"),
      "client_addr" => $addr,
      "client_port" => $port,
      "suppressHeaders" => 1,
    );

    $url = $this->call_url."/index.php?token=$this->token";
    $this->requestHttpPost($url, $post);
    
    return parent::onOpen($id, $addr, $port);
  }
  
  /**
   * Connection cleanup callback
   * 
   * @param integer $id The client's ID
   * 
   * @return void
   */
  function onCleanup($id) {
    $client = $this->clients[$id];
    
    $post = array(
      "m"       => $this->module,
      "dosql"   => $this->controller,
      "port"    => $this->port,
      "message" => base64_encode("TCP_Closed"),
      "client_addr" => $client["addr"],
      "client_port" => $client["port"],
      "suppressHeaders" => 1,
    );
      $url = $this->call_url."/index.php?token=$this->token";
    $this->requestHttpPost($url, $post);
    
    parent::onCleanup($id);
  }
  
  /**
   * Format the acknowledgement
   * 
   * @param string  $ack     The acknowledgement
   * 
   * @param integer $conn_id The connection id
   * 
   * @return string
   */
  function formatAck($ack, $conn_id = null) {
    return $ack;
  }
  
  /**
   * Encode the request and return it
   * 
   * @param string $buffer The buffer
   * 
   * @return string
   */
  function encodeClientRequest($buffer) {
    return base64_encode($buffer);
  }

  /**
   * Decode the response and return it
   *
   * @param string $ack The response
   *
   * @return string
   */
  function decodeResponse($ack) {
    return base64_decode($ack);
  }
  
  /**
   * A sample Dicom message
   *  
   * @return string
   */
  final static function sampleMessage() {
    return "";
  }
}
