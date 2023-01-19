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
 * Represents a User Info PDU Item
 */
class CDicomPDUItemUserInfo extends CDicomPDUItem {
  
  /**
   * An array of differents items
   * 
   * @var array of CDicomPDUItem
   */
  public $sub_items = array();
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x50);
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
   * Set the different sub items
   * 
   * @param array $items Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   * For the sub items, the value must be an array,
   * with keys the type of the item, and for value and item array.
   * 
   * @return null
   */
  function setSubItems($items) {
    foreach ($items as $_class => $_data) {
      $this->sub_items[] = new $_class($_data);
    }
  }
  /**
   * Decode the User Information
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->sub_items = CDicomPDUItemFactory::decodeConsecutiveItemsByLength($stream_reader, $this->length);
  }
    
  /**
   * Encode the User Information
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   *  
   * @return null
   */ 
  function encodeItem(CDicomStreamWriter $stream_writer) {
    $items = fopen("php://temp", "w+");
    
    $items_stream = new CDicomStreamWriter($items);
    foreach ($this->sub_items as $sub_item) {
      $sub_item->encodeItem($items_stream);
    }
    
    $this->calculateLength();
    
    $stream_writer->writeUInt8($this->type);
    $stream_writer->skip(1);
    $stream_writer->writeUInt16($this->length);
    $stream_writer->write($items_stream->buf);
    fclose($items);
  }
  
  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->length = 0;
    
    foreach ($this->sub_items as $sub_item) {
      $this->length += $sub_item->getTotalLength();
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
    $str = "User informations : 
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>";
    foreach ($this->sub_items as $item) {
      $str .= "<li>{$item->__toString()}</li>";
    }
    $str .= "</ul>";
    return $str;
  }
}