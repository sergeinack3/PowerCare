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
 * Represents a Implementation Version Name PDU Item
 */
class CDicomPDUItemImplementationVersionName extends CDicomPDUItem {
  
  /**
   * The version name
   * 
   * @var integer
   */
  public $version_name;
  
  /**
   * The constructor.
   * 
   * @param array $datas The datas, default null. 
   */
  function __construct($datas = array()) {
    $this->setType(0x55);
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
   * Set the version name
   * 
   * @param string $version_name The version name
   * 
   * @return null
   */
  function setVersionName($version_name) {
    $this->version_name = $version_name;
  }
  
  /**
   * Return the version name
   * 
   * @return array
   */
  function getValues() {
    return array("version_name" => $this->version_name);
  }
  
  /**
   * Decode the Implementation Version Name
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->version_name = $stream_reader->readString($this->length);
  }
  
  /**
   * Encode the Implementation Version Name
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
    $stream_writer->writeString($this->version_name, $this->length);
  }

  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->length = strlen($this->version_name);
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
    return "Implementation version name :
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>
              <li>Version name : $this->version_name</li>
            </ul>";
  }
}