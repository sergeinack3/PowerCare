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
class CDicomPDUItemPresentationContext extends CDicomPDUItem {
  
  /**
   * The id of the presentation context
   * 
   * @var integer
   */
  public $id;
  
  /**
   * The abstract syntax
   * 
   * @var CDicomPDUItemAbstractSyntax
   */
  public $abstract_syntax;
  
  /**
   * The transfer syntaxes
   * 
   * @var CDicomPDUItemTransferSyntax[]
   */
  public $transfer_syntaxes = array();

  /**
   * @var int
   */
  public $reason;
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x20);
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
   * @return void
   */
  function setId($id) {
    $this->id = $id;
  }
  
  /**
   * Set the reason
   * 
   * @param integer $reason The reason
   *  
   * @return void
   */
  function setReason($reason) {
    $this->reason = $reason;
  }
  
  /**
   * Set the abstract syntax
   * 
   * @param array $datas The data for create the abstract syntax
   * 
   * @return void
   */
  function setAbstractSyntax($datas) {
    $this->abstract_syntax = new CDicomPDUItemAbstractSyntax($datas);
  }
  
  /**
   * Set the transfer syntaxes
   * 
   * @param array $transfer_syntaxes The datas for create the transfer syntaxes
   * 
   * @return void
   */
  function setTransferSyntaxes($transfer_syntaxes) {
    foreach ($transfer_syntaxes as $datas) {
      $this->transfer_syntaxes[] = new CDicomPDUItemTransferSyntax($datas);
    }
  }
  
  /**
   * Decode the Presentation Syntax
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return void
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->id = $stream_reader->readUInt8();
    $stream_reader->skip(3);
    
    $this->abstract_syntax = CDicomPDUItemFactory::decodeItem($stream_reader);
    $this->transfer_syntaxes = CDicomPDUItemFactory::decodeConsecutiveItemsByType($stream_reader, 0x40);
  }
  
  /**
   * Encode the Presentation Syntax
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   *  
   * @return void
   */ 
  function encodeItem(CDicomStreamWriter $stream_writer) {
    $this->calculateLength();
    
    $stream_writer->writeUInt8($this->type);
    $stream_writer->skip(1);
    $stream_writer->writeUInt16($this->length);
    $stream_writer->writeUInt8($this->id);
    $stream_writer->skip(3);
    $this->abstract_syntax->encodeItem($stream_writer);
    foreach ($this->transfer_syntaxes as $transfer_syntax) {
      $transfer_syntax->encodeItem($stream_writer);
    }
  }

  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return void
   */
  function calculateLength() {
    $this->length = 4 + $this->abstract_syntax->getTotalLength();
    foreach ($this->transfer_syntaxes as $transfer_syntax) {
      $this->length += $transfer_syntax->getTotalLength();
    }
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
    $str = "Presentation Context : 
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>
              <li>id : $this->id</li>
              <li>{$this->abstract_syntax->__toString()}</li>";
    foreach ($this->transfer_syntaxes as $transfer_syntax) {
      $str .= "<li>{$transfer_syntax->__toString()}</li>";
    }
    $str .= "</ul>";
    return $str;
  }
}