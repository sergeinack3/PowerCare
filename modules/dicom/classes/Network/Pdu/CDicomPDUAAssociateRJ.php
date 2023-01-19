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
 * An A-Associate-RJ PDU
 */
class CDicomPDUAAssociateRJ extends CDicomPDU {
  
  /**
   * The result of the association request.
   * See $result_enum for possible values
   * 
   * @var integer
   */
  public $result;
  
  /**
   * Possible values for the field $result
   * 
   * @var array
   */
  static $result_enum = array(
    1 => "rejected-permanent",
    2 => "rejected-transient",
  );
  
  /**
   * Identify the creating source of the result and diagnostic fields.
   * See $source_enum for possible values
   * 
   * @var integer
   */
  public $source;
  
  /**
   * Possible values for the field $source
   * 
   * @var array
   */
  static $source_enum = array(
    1 => "Dicom-UL-service-user",
    2 => "Dicom-UL-service-provider-ACSE",
    3 => "Dicom-UL-service-provider-Pres" ,
  );
  
  /**
   * Identify the reason of the reject.
   * See $diagnostic_enum for possible values
   * 
   * @var integer
   */
  public $diagnostic;
  
  /**
   * Possible values for the field $diagnostic
   * 
   * @var array
   */
  static $diagnostic_enum = array(
    1 => array(
      1 => "no-reason",
      2 => "application-context-name-not-supported",
      3 => "calling-AE-title-not-recognized",
      7 => "called-AE-title-not-recognized",
    ),
    2 => array(
      1 => "no-reason",
      2 => "protocole-version-not-supported",
    ),
    3 => array(
      1 => "temporary-congestion",
      2 => "local-limit-exceeded",
    ),
  );
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x03);
    $this->setTypeStr("A-Associate-RJ");
    foreach ($datas as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
  }
  
  /**
   * Set the source
   * 
   * @param integer $source The source, see $source_enum for the different values
   *  
   * @return null
   */
  function setSource($source) {
    $this->source = $source;
  }
  
  /**
   * Set the result
   * 
   * @param integer $result The result, see $result_enum for the different values
   * 
   * @return null
   */
  function setResult($result) {
    $this->result = $result;
  }
  
  /**
   * Set the diagnostic
   * 
   * @param integer $diagnostic The diagnostic, see $diagnostic_enum for the different values
   * 
   * @return null
   */
  function setDiagnostic($diagnostic) {
    $this->diagnostic = $diagnostic;
  }
  
  /**
   * Decode the PDU
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   *  
   * @return null
   */
  function decodePDU(CDicomStreamReader $stream_reader) {
    $stream_reader->skip(1);
    $this->result = $stream_reader->readUInt8();
    $this->source = $stream_reader->readUInt8();
    $this->diagnostic = $stream_reader->readUInt8();
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
    $stream_writer->skip(1);
    $stream_writer->writeUInt8($this->result);
    $stream_writer->writeUInt8($this->source);
    $stream_writer->writeUInt8($this->diagnostic);
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
    $str = "<h1>A-Associate-RJ</h1><br>
            <ul>
              <li>Type : " . sprintf("%02X", $this->type) . "</li>
              <li>Length : $this->length</li>
              <li>Result : " . self::$result_enum[$this->result] . "</li>
              <li>Source : " . self::$source_enum[$this->source] . "</li>
              <li>Diagnostic : " . self::$diagnostic_enum[$this->source][$this->diagnostic] . "</li>
            </ul>";
    echo $str;
  }
}