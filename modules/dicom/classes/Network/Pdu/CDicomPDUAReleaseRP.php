<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network\Pdu;

use Ox\Interop\Dicom\CDicomStreamReader;
use Ox\Interop\Dicom\CDicomStreamWriter;

/**
 * An A-Release-RP PDU
 */
class CDicomPDUAReleaseRP extends CDicomPDU {

  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x06);
    $this->setTypeStr("A-Release-RP");
    foreach ($datas as $key => $value) {
      $words = explode('_', $key);
      $method = 'set';
      foreach ($words as $_word) {
        $method .= ucfirst($_word);
      }
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
  }
    
  /**
   * Decode the PDU
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   *  
   * @return null
   */
  function decodePDU(CDicomStreamReader $stream_reader) {
    $stream_reader->skip(4);
  }
  
  /**
   * Encode the PDU
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   *  
   * @return null
   */
  function encodePDU(CDicomStreamWriter $stream_writer) {
    $this->calculateLength();
    
    $stream_writer->writeUInt8($this->type);
    $stream_writer->skip(1);
    $stream_writer->writeUInt32($this->length);
    $stream_writer->skip(4);
  }
  
  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->length = 4;
  }

  /**
   * Return the total length, in number of bytes
   * 
   * @return integer
   */
  function getTotalLength() {
    if (!$this->length) {
      $this->calculateLength();
    }
    return $this->length + 6;
  }
  
  /**
   * Return a string representation of the class
   * 
   * @return string
   */
  function toString() {
    $str = "<h1>A-Release-RP</h1><br>
            <ul>
              <li>Type : " . sprintf("%02X", $this->type) . "</li>
              <li>Length : $this->length</li>
            </ul>";
    echo $str;
  }
}