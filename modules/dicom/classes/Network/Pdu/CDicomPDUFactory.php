<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network\Pdu;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Dicom\CDicomStreamReader;
use Ox\Interop\Dicom\CDicomStreamWriter;

/**
 * The PDU Factory, who matches the type of PDU and the corresponding PHP class
 * 
 * @todo Changer $pdu_types en un switch, et lever une exception en cas de type inconnu 
 */
class CDicomPDUFactory implements IShortNameAutoloadable {
  
  /**
   * Get the type of the PDU, and create the corresponding CDicomPDU
   * 
   * @param string $pdu_content           The datas sent by the client
   * 
   * @param array  $presentation_contexts The presentation contexts
   * 
   * @return CDicomPDU The PDU
   */
  static function decodePDU($pdu_content, $presentation_contexts = null) {
    $stream = fopen("php://temp", 'w+');
    fwrite($stream, $pdu_content);
    
    $stream_reader = new CDicomStreamReader($stream);
    $stream_reader->rewind();
    
    $pdu_class = self::readType($stream_reader);
    if ($pdu_class === false) {
      return null;
    }
    $length = self::readLength($stream_reader);

    $pdu = new $pdu_class(array("length" => $length));
    
    if ($pdu_class == "CDicomPDUPDataTF") {
      $pdu->setPresentationContexts($presentation_contexts);
    }
    
    $pdu->decodePDU($stream_reader);
    
    $stream_reader->close();
    
    $pdu->setPacket($stream_reader->buf);
    
    return $pdu;
  }
  
  /**
   * Create a PDU of the given type
   * 
   * @param string $type                  The type of the PDU you want to create
   * 
   * @param array  $datas                 The differents datas of the PDU
   * 
   * @param array  $presentation_contexts The presentation context
   * 
   * @return CDicomPDU The PDU
   */
  static function encodePDU($type, $datas = array(), $presentation_contexts = null) {
    $stream = fopen("php://temp", 'w+');
    
    $stream_writer = new CDicomStreamWriter($stream);

    $pdu_class = self::getPDUClass($type);
    
    $pdu = new $pdu_class($datas);
    
    if ($pdu_class == "CDicomPDUPDataTF") {
      $pdu->setPresentationContexts($presentation_contexts);
    }
    
    $pdu->encodePDU($stream_writer);
    
    $pdu->setPacket($stream_writer->buf);
    $stream_writer->close();
    
    return $pdu;
  }
  
  /**
   * Read the type of the PDU from the stream
   * 
   * @param CDicomStreamReader $stream The stream reader
   * 
   * @return string
   */
  static function readType(CDicomStreamReader $stream) {
    $tmp = $stream->readUInt8();
    $stream->skip(1);
    return self::getPDUClass($tmp);
  }
  
  /**
   * Read the length of the PDU from the stream
   * 
   * @param CDicomStreamReader $stream The stream reader
   * 
   * @return integer
   */
  static function readLength(CDicomStreamReader $stream) {
    return $stream->readUInt32();
  }
  
  /**
   * Make the link between the code types and the PDU classes
   * 
   * @param string $type The type of PDU
   * 
   * @return string
   */
  static function getPDUClass($type) {
    switch ($type) {
      case 0x01 :
        return "CDicomPDUAAssociateRQ";
      case 0x02 :
        return "CDicomPDUAAssociateAC";
      case 0x03 :
        return "CDicomPDUAAssociateRJ";
      case 0x04 :
        return "CDicomPDUPDataTF";
      case 0x05 :
        return "CDicomPDUAReleaseRQ";
      case 0x06 :
        return "CDicomPDUAReleaseRP";
      case 0x07 :
        return "CDicomPDUAAbort";
      default:
        return false;
    }
  }
}
