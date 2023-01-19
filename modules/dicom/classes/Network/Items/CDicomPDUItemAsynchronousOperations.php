<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network\Items;

use Ox\Interop\Dicom\CDicomStreamReader;
use Ox\Interop\Dicom\CDicomStreamWriter;

/**
 * Represents a Implementation Class UID PDU Item
 */
class CDicomPDUItemAsynchronousOperations extends CDicomPDUItem {
  
  /**
   * The maximum number of operations the AE may invoke
   * 
   * @var integer
   */
  public $max_number_operations_invoked;
  
  /**
   * The maximum number of operations the AE may perform
   * 
   * @var integer
   */
  public $max_number_operations_performed;
  
  /**
   * The constructor.
   * 
   * @param array $datas The datas, default null. 
   */
  function __construct($datas = array()) {
    $this->setType(0x53);
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
   * Set the maximum number of operation invoked
   * 
   * @param int $max_number The maximum number
   * 
   * @return null
   */
  function setMaxNumberOperationsInvoked($max_number) {
    $this->max_number_operations_invoked = $max_number;
  }
  
  /**
   * Set the maximum number of operation performed
   * 
   * @param int $max_number The maximum number
   * 
   * @return null
   */
  function setMaxNumberOperationsPerformed($max_number) {
    $this->max_number_operations_performed = $max_number;
  }
  
  /**
   * Return the values of the fields
   * 
   * @return array
   */
  function getValues() {
    return array(
      "max_number_operations_invoked" => $this->max_number_operations_invoked,
      "max_number_operations_performed" => $this->max_number_operations_performed
    );
  }
  
  /**
   * Decode the asynchronous Operations
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->max_number_operations_invoked = $stream_reader->readUInt16();
    $this->max_number_operations_performed = $stream_reader->readUInt16();
  }
  
  /**
   * Encode the Asynchronous Operations
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   *  
   * @return null
   */
  function encodeItem(CDicomStreamWriter $stream_writer) {
    $this->calculateLength();
    
    $stream_writer->writeUInt8($this->type);
    $stream_writer->skip(1);
    $stream_writer->writeUInt16($this->length);
    $stream_writer->writeUInt16($this->max_number_operations_invoked);
    $stream_writer->writeUInt16($this->max_number_operations_performed);
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
    return $this->length + 4;
  }

  /**
   * Return a string representation of the class
   * 
   * @return string
   */
  function __toString() {
    return "Asynchronous operations window negociation : 
      <ul>
        <li>Item type : " . sprintf("%02X", $this->type) . "</li>
        <li>Item length : $this->length</li>
        <li>Maximum number of operation invoked : $this->max_number_operations_invoked</li>
        <li>Maximum number of operation performed : $this->max_number_operations_performed</li>
      </ul>";
  }
}