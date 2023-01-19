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
 * Represents a SOP class common extended negociation item
 */
class CDicomPDUItemSopClassCommonExtendedNegociation extends CDicomPDUItem {
  
  /**
   * The sOP class uid length
   * 
   * @var integer
   */
  public $sop_uid_length;
  
  /**
   * The SOP class uid
   * 
   * @var string
   */
   public $sop_class_uid;
   
   /**
   * The service class uid length
   * 
   * @var integer
   */
  public $service_uid_length;
  
  /**
   * The Service class uid
   * 
   * @var string
   */
   public $service_class_uid;
   
   /**
   * The uid length
   * 
   * @var integer
   */
  public $related_sop_classes_id_length;
  
  /**
   * The related SOP class identification length
   * 
   * @var array
   */
  public $related_sop_classes_id = array();
   
  /**
   * The constructor.
   * 
   * @param array $datas The datas, default null. 
   */
  function __construct($datas = array()) {
    $this->setType(0x57);
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
  function setSopUidLength($uid_length) {
    $this->sop_uid_length = $uid_length;
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
   * Set the service SOP class uid length
   * 
   * @param integer $service_uid_length The service uid length
   * 
   * @return null
   */
  function setServiceUidLength($service_uid_length) {
    $this->service_uid_length = $service_uid_length;
  }
  
  /**
   * Set the service SOP class UID
   * 
   * @param string $service_class_uid The service SOP class UID
   * 
   * @return null
   */
  function setServiceClassUid($service_class_uid) {
    $this->service_class_uid = $service_class_uid;
  }
  
  /**
   * Set the length of the general related SOP classes id field
   * 
   * @param integer $length The length
   * 
   * @return null
   */
  function setRelatedSopClassesIdLength($length) {
    $this->related_sop_classes_id_length = $length;
  }
  
  /**
   * Set the general related SOP classes id
   * 
   * @param array $related_sop_classes_id An array who contains the related SOP classes UIDs
   * 
   * @return null
   */
  function setRelatedSopClassesId($related_sop_classes_id) {
    $this->related_sop_classes_id = array();
    foreach ($related_sop_classes_id as $_id) {
      $this->related_sop_classes_id[] = new CDicomSubFieldRelatedSopClassIdentification($_id);
    }
  }
  
  /**
   * Return the values of the fields
   * 
   * @return array
   */
  function getValues() {
    $rel_sop_classes_id = array();
    foreach ($this->related_sop_classes_id as $_related_sop_class_id) {
      $rel_sop_classes_id[] = $_related_sop_class_id->getClassUID();
    }
    return array(
      "sop_uid_length"     => $this->sop_uid_length,
      "sop_class_uid" => $this->sop_class_uid,
      "service_uid_length"       => $this->service_uid_length,
      "service_class_uid"       => $this->service_class_uid,
      "related_sop_classes_id_length" => $this->related_sop_classes_id_length,
      "related_sop_classes_id" => $rel_sop_classes_id,
    );
  }
  
  /**
   * Decode the SOP class common extended negociation item
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decodeItem(CDicomStreamReader $stream_reader) {
    $this->sop_uid_length = $stream_reader->readUInt16();
    $this->sop_class_uid = $stream_reader->readUID($this->sop_uid_length);
    $this->service_uid_length = $stream_reader->readUInt16();
    $this->service_class_uid = $stream_reader->readUID($this->service_uid_length);
    $this->related_sop_classes_id_length = $stream_reader->readUInt16();
    
    if ($this->related_sop_classes_id_length > 0) {
      $related_sop_classes_content = $stream_reader->read($this->related_sop_classes_id_length);
      
      $handle = fopen("php://temp", "w+");
      fwrite($handle, $related_sop_classes_content);
      $related_sop_classes_stream = new CDicomStreamReader($handle);
      
      $this->related_sop_classes_id = array();
      while ($related_sop_classes_stream->tell() <= $this->related_sop_classes_id_length) {
        $related_sop_class_id = new CDicomSubFieldRelatedSopClassIdentification();
        $related_sop_class_id->decodeField($related_sop_classes_stream);
      }
    }
  }
  
  /**
   * Encode the SOP class common extended negociation item
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
    $stream_writer->writeUInt16($this->sop_uid_length);
    $stream_writer->writeUID($this->sop_class_uid, $this->sop_uid_length);
    $stream_writer->writeUInt16($this->service_uid_length);
    $stream_writer->writeUID($this->service_class_uid, $this->service_uid_length);
    $stream_writer->writeUInt16($this->related_sop_classes_id_length);
    
    foreach ($this->related_sop_classes_id as $_related_sop_class_id) {
      $_related_sop_class_id->encodeItem($stream_writer);
    }
  }

  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->sop_uid_length = strlen($this->sop_class_uid);
    $this->service_uid_length = strlen($this->service_class_uid);
    $this->related_sop_classes_id_length = 0;
    foreach ($this->related_sop_classes_id as $_related_sop_class_id) {
      $this->related_sop_classes_id_length += $_related_sop_class_id->getLength();
    }
    $this->length = 6 + $this->sop_uid_length + $this->service_uid_length + $this->related_sop_classes_id_length;
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
    $str = "Sop class common extended negociation :
            <ul>
              <li>Item type : " . sprintf("%02X", $this->type) . "</li>
              <li>Item length : $this->length</li>
              <li>SOP class UID length : $this->sop_uid_length</li>
              <li>SOP class UID : $this->sop_class_uid</li>
              <li>Service class UID length : $this->service_uid_length</li>
              <li>Service class UID : $this->service_class_uid</li>
              <li>Related general SOP classes UIDs length : $this->related_sop_classes_id_length</li>";
    if ($this->related_sop_classes_id_length == 0) {
      return "$str</ul>";
    }
    $str .=   "<li>Related generam SOP classes :<ul>";
    foreach ($this->related_sop_classes_id as $_sop_class_id) {
      $str .= "<li>{$_sop_class_id->__toString()}</li>";
    }       
    return "$str</ul></li></ul>";
  }
}