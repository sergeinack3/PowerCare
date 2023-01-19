<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use Ox\Core\CMbDT;
use Ox\Core\CSocketBasedServer;

/**
 * A MLLP server listening with a socket of a port
 */
class CMLLPServer extends CSocketBasedServer {
  
  /**
   * The module 
   * 
   * @var string
   */
  protected $module = "eai";
  
  /**
   * The controller who will receive the messages
   * 
   * @var string
   */
  protected $controller = "do_receive_mllp";

  /**
   * Check if the message is complete
   * 
   * @param string $message The message
   * 
   * @return boolean
   */
  function isMessageFull($message) {
    $message = trim($message);
    return strrpos($message, "\x1C") === strlen($message)-1;
  }

  /**
   * @see parent::displayMessage
   */
  function displayMessage($message, $header = null) {
    if ($header) {
      $this->out(" ----- $header ----- ", 0);
    }

    $message = preg_replace("/[\r\n]+/", "\n", $message);

    $colors = array(
      "|" => "red",
      "^" => "green",
      "~" => "blue",
      "&" => "magenta",
    );

    //$message = preg_replace('/([^\|\^\~\&]+)/', shColorText($message, $_color, "white"), $message);

    foreach ($colors as $_char => $_color) {
      $message = str_replace($_char, shColorText($_char, $_color), $message);
    }

    echo "$message\n ------------------ \n";
  }

  /**
   * Format the buffer
   *
   * @param string $buffer The buffer
   *
   * @return string
   */
  function appendRequest($buffer) {
    return "$buffer";
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
    return "\x0B$ack\x1C\x0D";
  }
  
  /**
   * Check if the request is a header message
   * 
   * @param string $request The request
   * 
   * @return boolean
   */
  function isHeader($request) {
    return strpos($request, "\x0B") === 0;
  }

  /**
   * Encode the request and return it
   *
   * @param string $buffer The buffer
   *
   * @return string
   */
  function encodeClientRequest($buffer) {
    return rtrim($buffer, "\r\n\t\0\x0B\x1C");
  }

  /**
   * A sample ORU message formatted in ER7
   * 
   * @return string A sample ORU message formatted in ER7
   */
  final static function sampleMessage(){
    $date = CMbDT::strftime("%Y%m%d%H%M%S");
    $er7 = <<<ER7
MSH|^~\&|||||||ORU^R01|HP104220879017992|P|2.3||||||8859/1
PID|1||000038^^^&&^PI~323328^^^Mediboard&1.2.250.1.2.3.4&OX^RI||TEST^Obx^^^m^^L^A||19800101|M|||^^^^^^H|||||||12000041^^^&&^AN||||||||||||N||VALI|20120116161701||||||
PV1|1|I|UF1^^^&&^O|R|12000041^^^&&^RI||929997607^FOO^Bar^^^^^^&1.2.250.1.71.4.2.1&ISO^L^^^ADELI^^^^^^^^^^|||||||90||P|929997607^FOO^Bar^^^^^^&1.2.250.1.71.4.2.1&ISO^L^^^ADELI^^^^^^^^^^||321120^^^Mediboard&1.2.250.1.2.3.4&OX^RI||AMBU|N||||||||||||||4||||||||||||||||
OBR||||Mediboard test|||$date

ER7;
    
    $obx = array();
    $obx[] = "OBX||NM|0002-4b60^Tcore^MDIL|0|".(rand(350, 400)/10)."|0004-17a0^°C^MDIL|||||F";
    $obx[] = "OBX||NM|0002-4bb8^SpO2^MDIL|0|".  rand(80, 100).     "|0004-0220^%^MDIL|||||F";
    $obx[] = "OBX||NM|0002-5000^Resp^MDIL|0|".  rand(20, 50).      "|0004-0ae0^rpm^MDIL|||||F";
    $obx[] = "OBX||NM|0002-4182^HR^MDIL|0|".    rand(40, 90).      "|0004-0aa0^bpm^MDIL|||||F";
    
    $obx[] = "OBX||NM|0002-4a15^ABPs^MDIL|0|".    rand(90, 160).   "|0004-0f20^mmHg^MDIL|||||F";
    $obx[] = "OBX||NM|0002-4a16^ABPd^MDIL|0|".    rand(30, 90).    "|0004-0f20^mmHg^MDIL|||||F";
    $obx[] = "OBX||NM|0002-4a17^ABPm^MDIL|0|".    rand(80, 100).   "|0004-0f20^mmHg^MDIL|||||F";
    
    $er7 .= implode("\n", $obx);
    
    return "\x0B$er7\x1C\x0D";
  }
}
