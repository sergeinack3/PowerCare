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
 * An A-Abort PDU
 */
class CDicomPDUAAbort extends CDicomPDU {
  
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
    0 => "Dicom-UL-service-user",
    2 => "Dicom-UL-service-provider" ,
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
    2 => array(
      0 => "reason-not-specified",
      1 => "unrecognized-PDU",
      2 => "unexpected-PDU",
      4 => "unrecognized-PDU-parameter",
      5 => "unexpected-PDU-parameter",
      6 => "invalide-PDU-parameter-value"
    )
  );
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x07);
    $this->setTypeStr("A-Abort");
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
    $stream_reader->skip(2);
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
    
    $stream_writer->writeUInt16($this->type);
    $stream_writer->skip(1);
    $stream_writer->writeUInt32($this->length);
    $stream_writer->skip(2);
    $stream_writer->writeUInt8($this->source);
    if ($this->source == 0) {
      $stream_writer->skip(1);
    }
    else {
      $stream_writer->writeUInt8($this->diagnostic);
    }
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
    $str = "<h1>A-Abort</h1><br>
            <ul>
              <li>Type : " . sprintf("%02X", $this->type) . "</li>
              <li>Length : $this->length</li>
              <li>Source : " . self::$source_enum[$this->source] . "</li>
              <li>Diagnostic : ";
    if ($this->source == 0) {
      $str .= "non significant</li></ul>";
    }
    else {
      $str .= self::$diagnostic_enum[$this->source][$this->diagnostic] . "</li></ul>";
    }          
    echo $str;
  }
}