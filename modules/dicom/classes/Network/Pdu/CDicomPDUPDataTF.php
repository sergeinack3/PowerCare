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
use Ox\Interop\Dicom\Network\Messages\CDicomPDV;

/**
 * An P-Data-TF PDU
 */
class CDicomPDUPDataTF extends CDicomPDU {
 
  /**
   * The presentation data value
   * 
   * @var CDicomPDV[]
   */
  protected $pdvs = array();
  
  /**
   * The presentation contexts
   * 
   * @var array 
   */
  protected $presentation_contexts = null;
 
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
    $this->setType(0x04);
    $this->setTypeStr("P-Data-TF");
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
   * Set a PDV
   * 
   * @param array $pdv_datas The pdv datas
   * 
   * @return null
   */
  function setPDV($pdv_datas) {
    $this->pdvs[] = new CDicomPDV($pdv_datas);
  }
  
  /**
   * Return a PDV
   *
   * @param integer $index The index of the PDV
   * 
   * @return CDicomPDV
   */
  function getPDV($index) {
    return $this->pdvs[$index];
  }

  /**
   * Set the PDVs
   *
   * @param array $pdvs An array of pdv datas array
   *
   * @return void
   */
  function setPDVs($pdvs) {
    $this->pdvs = array();
    foreach ($pdvs as $_pdv_datas) {
      $this->setPDV($_pdv_datas);
    }
  }

  /**
   * Return the PDVs
   *
   * @return CDicomPDV[]
   */
  function getPDVs() {
    return $this->pdvs;
  }
  
  /**
   * Return the presentation contexts
   * 
   * @return array 
   */
  function getPresentationContexts() {
    return $this->presentation_contexts;
  }
  
  /**
   * Set the transfer syntax
   * 
   * @param array $presentation_contexts The presentation contexts
   * 
   * @return null
   */
  function setPresentationContexts($presentation_contexts) {
    $this->presentation_contexts = $presentation_contexts;
  }
  
  /**
   * Calculate the length of the item (without the type and the length fields)
   * 
   * @return null
   */
  function calculateLength() {
    $this->length = 0;
    foreach ($this->pdvs as $_pdv) {
      $this->length = $_pdv->getTotalLength();
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
    return $this->length + 6;
  }
  
  /**
   * Decode the PDU
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   *  
   * @return null
   */
  function decodePDU(CDicomStreamReader $stream_reader) {
    $this->pdvs = array();
    $stream_reader->setStreamLength($this->getTotalLength());

    while ($stream_reader->getPos() < $stream_reader->getStreamLength()) {
      $pdv_length = $stream_reader->readUInt32();// + 4;
      //$stream_reader->seek(-4);
      $pdv_content = $stream_reader->read($pdv_length);
      $pdv_handle = fopen('php://temp', 'w+');
      fwrite($pdv_handle, $pdv_content, $pdv_length);
      $pdv_stream = new CDicomStreamReader($pdv_handle);
      $pdv_stream->rewind();
      $pdv = new CDicomPDV(array("presentation_contexts" => $this->presentation_contexts, "length" => $pdv_length));
      $pdv->decode($pdv_stream);
      $this->pdvs[] = $pdv;
    }
  }
  
  /**
   * Encode the PDU
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   *  
   * @return null
   */
  function encodePDU(CDicomStreamWriter $stream_writer) {
    $handle = fopen("php://temp", "w+");
    $pdv_stream = new CDicomStreamWriter($handle);

    foreach ($this->pdvs as $_pdv) {
      $_pdv->setPresentationContexts($this->presentation_contexts);
      $_pdv->encode($pdv_stream);
    }
    
    $this->calculateLength();
    
    $stream_writer->writeUInt8($this->type);
    $stream_writer->skip(1);
    $stream_writer->writeUInt32($this->length);
    $stream_writer->write($pdv_stream->buf);
  }
  
  /**
   * Return a string representation of the class
   * 
   * @return string
   */
  function toString() {
    $str = "<h1>P-Data-TF</h1><br>
            <ul>
              <li>Type : " . sprintf("%02X", $this->type) . "</li>
              <li>Length : $this->length</li>";
    foreach ($this->pdvs as $_pdv) {
      $str .= "<li>" . $_pdv->__toString() . "</li>
            </ul>";
    }
    echo $str;
  }
}