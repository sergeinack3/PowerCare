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
 * Represents a Presentation Syntax PDU Item
 */
class CDicomPDUItemPresentationContextReply extends CDicomPDUItem {

  /**
   * The id of the presentation context
   * 
   * @var integer
   */
  public $id;
  
  /**
   * The acceptance or the rejection of the transfer sybtax, and the reason if the rejected.
   * See $reason_enum for the different values and their signification
   * 
   * @var integer
   */
  public $reason;
  
  /**
   * Possible values for the field $reason
   * 
   * @var array
   */
  static $reason_enum = array(
    0 => "acceptance",
    1 => "user-rejection",
    2 => "no-reason",
    3 => "abstract-syntax-not-supported",
    4 => "transfer-syntaxes-not-supported"
  );
  
  /**
   * The transfer syntax
   * 
   * @var CDicomPDUItemTransferSyntax
   */
  public $transfer_syntax;
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x21);
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
   * Set the id
   * 
   * @param integer $id The id
   *  
   * @return null
   */
  function setId($id) {
    $this->id = $id;
  }
  
  /**
   * Set the reason
   * 
   * @param integer $reason The reason
   *  
   * @return null
   */
  function setReason($reason) {
    $this->reason = $reason;
  }
  
  /**
   * Set the transfer syntax
   * 
   * @param array $datas The data for create the transfer syntax
   * 
   * @return null
   */
  function setTransferSyntax($datas) {
    $this->transfer_syntax = new CDicomPDUItemTransferSyntax($datas);
  }
  
  /**
   * Decode the Presentation Syntax
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->id = $stream_reader->readUInt8();
    $stream_reader->skip(1);
    $this->reason = $stream_reader->readUInt8();
    $stream_reader->skip(1);
    $this->transfer_syntax = CDicomPDUItemFactory::decodeItem($stream_reader);
  }
  
  /**
   * Encode the Presentation Syntax
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
    $stream_writer->writeUInt8($this->id);
    $stream_writer->skip(1);
    $stream_writer->writeUInt8($this->reason);
    $stream_writer->skip(1);
    $this->transfer_syntax->encodeItem($stream_writer);
  }
  
  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->length = 4 + $this->transfer_syntax->getTotalLength();
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
    $str = "Presentation context reply : 
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>
              <li>id : $this->id</li>
              <li>{$this->transfer_syntax->__toString()}</li>
            </ul>";
    return $str;
  }
}