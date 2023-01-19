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
use Ox\Interop\Dicom\Network\Items\CDicomPDUItemApplicationContext;
use Ox\Interop\Dicom\Network\Items\CDicomPDUItemFactory;
use Ox\Interop\Dicom\Network\Items\CDicomPDUItemPresentationContext;
use Ox\Interop\Dicom\Network\Items\CDicomPDUItemUserInfo;

/**
 * An A-Associate-RQ PDU
 */
class CDicomPDUAAssociateRQ extends CDicomPDU {
  
  /**
   * Protocol version, must be equal to 1
   * 
   * @var integer
   */
  public $protocol_version;
  
  /**
   * The called application entity
   * 
   * @var string
   */
  public $called_AE_title;
  
  /**
   * The calling application entity
   * 
   * @var string
   */
  public $calling_AE_title;
  
  /**
   * The application context
   * 
   * @var CDicomPDUItemApplicationContext
   */
  public $application_context;
  
  /**
   * The presentation contexts
   * 
   * @var array of CDicomPDUItemPresentationContextRQ
   */
  public $presentation_contexts = array();
  
  /**
   * The User informations
   * 
   * @var CDicomPDUItemUserInfo
   */
  public $user_info;
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x01);
    $this->setTypeStr("A-Associate-RQ");
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
   * Set the protocol version
   * 
   * @param integer $protocol_version The protocol_version
   *  
   * @return void
   */
  function setProtocolVersion($protocol_version) {
    $this->protocol_version = $protocol_version;
  }
  
  /**
   * Set the called AE title
   * 
   * @param integer $called_AE_title The called AE title
   * 
   * @return void
   */
  function setCalledAETitle($called_AE_title) {
    $this->called_AE_title = str_pad($called_AE_title, 16);
  }
  
  /**
   * Set the calling application entity
   * 
   * @param integer $calling_AE_title The calling AE title
   * 
   * @return void
   */
  function setCallingAETitle($calling_AE_title) {
    $this->calling_AE_title = str_pad($calling_AE_title, 16);
  }
  
  /**
   * Set the application context
   * 
   * @param array $datas The data for create the application context
   * 
   * @return void
   */
  function setApplicationContext($datas) {
    $this->application_context = new CDicomPDUItemApplicationContext($datas);
  }
  
  /**
   * Set the presentation context
   * 
   * @param array $pres_contexts The datas for create the transfer syntaxes
   * 
   * @return void
   */
  function setPresentationContexts($pres_contexts) {
    foreach ($pres_contexts as $datas) {
      $this->presentation_contexts[] = new CDicomPDUItemPresentationContext($datas);
    }
  }
  
  /**
   * Set the user informations
   * 
   * @param array $datas The data for create the user informations
   * 
   * @return void
   */
  function setUserInfo($datas) {
    $this->user_info = new CDicomPDUItemUserInfo($datas);
  }
  
  /**
   * Decode the PDU
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   *  
   * @return void
   */
  function decodePDU(CDicomStreamReader $stream_reader) {
    $this->protocol_version = $stream_reader->readUInt16();
    
    $stream_reader->skip(2);
    
    $this->called_AE_title = $stream_reader->readString(16);
    
    // On test si called_AE_title = AE title du serveur
    
    $this->calling_AE_title = $stream_reader->readString(16);
    
    
    // On passe 32 octets, réservés par Dicom
    $stream_reader->skip(32);
    
    $this->application_context = CDicomPDUItemFactory::decodeItem($stream_reader);
    $this->presentation_contexts = CDicomPDUItemFactory::decodeConsecutiveItemsByType($stream_reader, 0x20);
    $this->user_info = CDicomPDUItemFactory::decodeItem($stream_reader);
  }
  
  /**
   * Encode the PDU
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   *  
   * @return void
   */
  function encodePDU(CDicomStreamWriter $stream_writer) {
    $this->calculateLength();
    
    $stream_writer->writeUInt8($this->type);
    $stream_writer->skip(1);
    $stream_writer->writeUInt32($this->length);
    $stream_writer->writeUInt16($this->protocol_version);
    $stream_writer->skip(2);
    $stream_writer->writeString($this->called_AE_title, 16);
    $stream_writer->writeString($this->calling_AE_title, 16);
    $stream_writer->skip(32);
    $this->application_context->encodeItem($stream_writer);
    foreach ($this->presentation_contexts as $_item) {
      $_item->encodeItem($stream_writer);
    }
    $this->user_info->encodeItem($stream_writer);
  }

  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return void
   */
  function calculateLength() {
    $this->length = 68 + $this->application_context->getTotalLength();
    
    foreach ($this->presentation_contexts as $_item) {
      $this->length += $_item->getTotalLength();
    }
    
    $this->length += $this->user_info->getTotalLength();
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
    $str = "<h1>A-Associate-RQ</h1><br>
            <ul>
              <li>Type : " . sprintf("%02X", $this->type) . "</li>
              <li>Length : $this->length</li>
              <li>Called AE title : $this->called_AE_title</li>
              <li>Calling AE title : $this->calling_AE_title</li>
              <li>{$this->application_context->__toString()}</li>";
    foreach ($this->presentation_contexts as $pres_context) {
      $str .= "<li>{$pres_context->__toString()}</li>";
    }
    $str .= "<li>{$this->user_info->__toString()}</li></ul>";
    echo $str;
  }
}