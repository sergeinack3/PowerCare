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
 * Represents an User Identity Negociation RQ PDU Item
 */
class CDicomPDUItemUserIdentityNegociationRQ extends CDicomPDUItem {
  
  /**
   * The user identity types. 
   * See $user_identity_type_values for the significations
   * 
   * @var integer
   */
  public $user_identity_type;

  /**
   * An array who match the possible values for the user identity types and their signification
   */
  static $user_identity_type_values = array(
    1 => "Username as an UTF-8 string",
    2 => "Username as an UTF-8 string and passcode",
    3 => "Kerberos service ticket",
    4 => "SAML assertion"
  );
  
  /**
   * Positive response requested
   * 
   * @var integer
   */
  public $positive_response_requested;
  
  /**
   * An array who match the possible values for the field positive_response_requested and their signification
   */
  static $positive_response_requested_values = array(
    0 => "no response requested",
    1 => "positive response requested"
  );
  
  /**
   * The length of the primary field
   * 
   * @var integer
   */
  public $primary_field_length;
  
  /**
   * The user identity, in the type defined by the user identity type
   * 
   * @var string
   */
  public $primary_field;
  
  /**
   * The length of the secondary field. If the identity type is not "2", should be equal to 0
   * 
   * @var integer
   */
  public $secondary_field_length;
  
  /**
   * This field should be null if the identiy type is not equal to 2
   * 
   * @var string
   */
  public $secondary_field;
   
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType("58");
    foreach ($datas as $key => $value) {
      $method = 'set' . ucfirst($key);
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
  }
  
  /**
   * Set the user identity type
   * 
   * @param integer $identity_type The identity type
   *  
   * @return null
   */
  function setUserIdentityType($identity_type) {
    $this->user_identity_type = $identity_type;
  }
  
  /**
   * Set the field positive repsonse requested
   * 
   * @param integer $pos_res_req The value
   * 
   * @return null
   */
  function setPositiveResponseRequested($pos_res_req) {
    $this->positive_response_requested = $pos_res_req;
  }
  
  /**
   * Set the length of the primary field
   * 
   * @param integer $length The length
   * 
   * @return null
   */
  function setPrimaryFieldLength($length) {
    $this->primary_field_length = $length;
  }
  
  /**
   * Set the primary field
   * 
   * @param string $primary_field The primary field
   * 
   * @return null
   */
  function setPrimaryField($primary_field) {
    $this->primary_field = $primary_field;
  }
  
  /**
   * Set the length of the secondary field
   * 
   * @param integer $length The length
   * 
   * @return null
   */
  function setSecondaryFieldLength($length) {
    $this->secondary_field_length = $length;
  }
  
  /**
   * Set the secondary field
   * 
   * @param string $secondary_field The secondary field
   * 
   * @return null
   */
  function setSecondaryField($secondary_field) {
    $this->secondary_field = $secondary_field;
  }
  
  /**
   * Decode the User Identity Negociation RQ
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->user_identity_type = $stream_reader->readUInt8();
    $this->positive_response_requested = $stream_reader->readUInt8();
    $this->primary_field_length = $stream_reader->readUInt16();
    $this->primary_field = $stream_reader->readString($this->primary_field_length);
    $this->secondary_field_length = $stream_reader->readUInt16();
    if ($this->secondary_field_length > 0 ) {
      $this->secondary_field = $stream_reader->readString($this->secondary_field_length);
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
    $stream_writer->writeUInt8($this->user_identity_type);
    $stream_writer->writeUInt8($this->positive_response_requested);
    $stream_writer->writeUInt16($this->primary_field_length);
    $stream_writer->writeString($this->primary_field, $this->primary_field_length);
    $stream_writer->writeUInt16($this->secondary_field_length);
    if ($this->secondary_field_length > 0) {
      $stream_writer->writeString($this->secondary_field, $this->secondary_field_length);
    }
  }

  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->primary_field_length =  strlen($this->primary_field);
    if ($this->secondary_field) {
      $this->secondary_field_length = strlen($this->secondary_field);
    }
    else {
      $this->secondary_field_length = 0;
    }
    $this->length = 6 + $this->primary_field_length + $this->secondary_field_length;
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
    $str = "User identity negociation RQ :
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>
              <li>User identity type : " . self::$user_identity_type_values[$this->user_identity_type] . "</li>
              <li>Positive response requested : " . self::$positive_response_requested_values[$this->positive_response_requested] . "</li>
              <li>Primary field length : $this->primary_field_length</li>
              <li>Primary field : $this->primary_field</li>
              <li>Secondary field length : $this->secondary_field_length</li>";
    if ($this->secondary_field_length > 0) {
      $str .= "<li>Secondary field : $this->secondary_field</li>";
    }     
    return "$str</ul>";
  }
}