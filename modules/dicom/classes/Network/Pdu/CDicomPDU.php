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
 * Represent a DICOM PDU (Protocol Data Unit)
 */
class CDicomPDU implements IShortNameAutoloadable {
  
  /**
   * The type of the PDU, in number
   * 
   * @var string
   */
  public $type;
  
  /**
   * The type of the PDU, in string
   * 
   * @var string
   */
  public $type_str;
  
  /**
   * The length of the PDU
   * 
   * @var integer
   */
  public $length;
  
  /**
   * The encoded pdu
   * 
   * @var string
   */
  protected $packet = null;
  
  /**
   * Set the length
   * 
   * @param integer $length The length
   *  
   * @return null
   */
  function setLength($length) {
    $this->length = $length;
  }
  
  /**
   * Set the type
   * 
   * @param string $type The type of the PDU
   *  
   * @return null
   */
  function setType($type) {
    $this->type = $type;
  }
  
  /**
   * Set the type
   * 
   * @param string $type The type of the PDU
   *  
   * @return null
   */
  function setTypeStr($type) {
    $this->type_str = $type;
  }
  
  /**
   * Return the encoded pdu
   * 
   * @return string
   */
  function getPacket() {
    return $this->packet;
  }
  
  /**
   * Set the encoded pdu
   * 
   * @param string $packet The encoded packet
   * 
   * @return null
   */
  function setPacket($packet) {
    $this->packet = $packet;
  }
  
  /**
   * Decode the PDU
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodePDU(CDicomStreamReader $stream_reader) {
    
  }
  
  /**
   * Encode the PDU
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   * 
   * @return null
   */
  function encodePDU(CDicomStreamWriter $stream_writer) {
    
  }
}