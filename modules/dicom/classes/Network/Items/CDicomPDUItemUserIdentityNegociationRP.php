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
 * Represents an User Identity Negociation RP PDU Item
 */
class CDicomPDUItemUserIdentityNegociationRP extends CDicomPDUItem {
  
  /**
   * The length of the server response
   * 
   * @var integer
   */
  public $server_response_length;
  
  /**
   * The server response
   * 
   * @var string
   */
  public $server_response;
   
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x59);
    foreach ($datas as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
  }
  
  /**
   * Set the length of the server response
   * 
   * @param integer $length The length
   * 
   * @return null
   */
  function setServerResponseLength($length) {
    $this->server_response_length = $length;
  }
  
  /**
   * Set the server response
   * 
   * @param string $server_response The sever response
   * 
   * @return null
   */
  function setServerResponse($server_response) {
    $this->server_response = $server_response;
  }
  
  /**
   * Decode the User Identity Negociation RQ
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->server_response_length = $stream_reader->readUInt16();
    if ($this->server_response_length > 0 ) {
      $this->server_response = $stream_reader->readString($this->server_response_length);
    }
  }
  
  /**
   * Encode the User Identity Negociation RQ
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
    $stream_writer->writeUInt16($this->server_response_length);
    if ($this->server_response_length > 0) {
      $stream_writer->writeString($this->server_response, $this->server_response_length);
    }
  }

  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    if ($this->server_response) {
      $this->server_response_length = strlen($this->server_response);
    }
    else {
      $this->server_response_length = 0;
    }
    $this->length = 2 + $this->server_response_length;
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
    $str = "User identity negociation RP :
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>
              <li>Server response length : $this->server_response_length</li>";
    if ($this->server_response_length > 0) {
      $str .= "<li>Server response : $this->server_response</li>";
    }     
    return "$str</ul>";
  }
}