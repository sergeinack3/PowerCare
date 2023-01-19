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
 * Represents a Sop Class Extended Negociation PDU Item
 */
class CDicomPDUItemSopClassExtendedNegociation extends CDicomPDUItem {
  
  /**
   * The uid length
   * 
   * @var integer
   */
  public $uid_length;
  
  /**
   * The SOP class uid
   * 
   * @var string
   */
   public $sop_class_uid;
   
   /**
    * The Service Class Application level of support
    * 
    * @see PS 3.4, Annexe B.3.1
    * 
    * @var integer
    */
   public $sca_support;
   
   /**
    * The Service Class Application level of digital signature support
    * 
    * @see PS 3.4, Annexe B.3.1
    * 
    * @var integer
    */
   public $sca_digital_signature_support;
   
   /**
    * The Service Class Application element coercion
    * 
    * @see PS 3.4, Annexe B.3.1
    * 
    * @var integer
    */
   public $sca_element_coercion;
  
  /**
   * The constructor.
   * 
   * @param array $datas The datas, default null. 
   */
  function __construct($datas = array()) {
    $this->setType(0x56);
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
   * Set the uid length
   * 
   * @param integer $uid_length The uid length
   * 
   * @return null
   */
  function setUidLength($uid_length) {
    $this->uid_length = $uid_length;
  }
  
  /**
   * Set the SOP class UID
   * 
   * @param string $uid SOP class UID
   * 
   * @return null
   */
  function setSOPClassUid($uid) {
    $this->sop_class_uid = $uid;
  }
  
  /**
   * Set the SCA support level
   * 
   * @param integer $support The level of support
   * 
   * @return null
   */
  function setScaSupport($support) {
    $this->sca_support = $support;
  }
  
  /**
   * Set the SCA level of digital signature support
   * 
   * @param integer $support The level of digital signature support
   * 
   * @return null
   */
  function setScaDigitalSignatureSupport($support) {
    $this->sca_digital_signature_support = $support;
  }
  
  /**
   * Set the SCA element coercion
   * 
   * @param integer $element_coercion The element coercion
   * 
   * @return null
   */
  function setScaElementCoercion($element_coercion) {
    $this->sca_element_coercion = $element_coercion;
  }
  
  /**
   * Return the values of the fields
   * 
   * @return array
   */
  function getValues() {
    return array(
      "uid_length"                     => $this->uid_length,
      "sop_class_uid"                 => $this->sop_class_uid,
      "sca_support"                   => $this->sca_support,
      "sca_digital_signature_support"  => $this->sca_digital_signature_support,
      "sca_element_coercion"           => $this->sca_element_coercion
    );
  }
  
  /**
   * Decode the Sop Class Extended Negociation
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->uid_length = $stream_reader->readUInt16();
    $this->sop_class_uid = $stream_reader->readUID($this->uid_length);
    $this->sca_support = $stream_reader->readUInt8();
    $stream_reader->skip(1);
    $this->sca_digital_signature_support = $stream_reader->readUInt8();
    $stream_reader->skip(1);
    $this->sca_element_coercion = $stream_reader->readUInt8();
    $stream_reader->skip(1);
  }
  
  /**
   * Encode the Sop Class Extended Negociation
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
    $stream_writer->writeUInt16($this->uid_length);
    $stream_writer->writeUID($this->sop_class_uid, $this->uid_length);
    $stream_writer->writeUInt8($this->sca_support);
    $stream_writer->skip(1);
    $stream_writer->writeUInt8($this->sca_digital_signature_support);
    $stream_writer->skip(1);
    $stream_writer->writeUInt8($this->sca_element_coercion);
    $stream_writer->skip(1);
  }

  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->uid_length = strlen($this->sop_class_uid);
    $this->length = 8 + $this->uid_length;
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
    return "SOP class extended negociation : 
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>
              <li>SOP class UID length : $this->uid_length</li>
              <li>SOP class UID : $this->sop_class_uid</li>
              <li>
                Service Class Application :
                <ul>
                  <li>Level of support : $this->sca_support</li>
                  <li>Level of digital signature support : $this->sca_digital_signature_support</li>
                  <li>Element coercion : $this->sca_element_coercion</li>
                </ul>
              </li>
            </ul>";
  }
}