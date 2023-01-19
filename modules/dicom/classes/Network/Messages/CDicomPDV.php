<?php
/**
 * @package Mediboard\Dicom
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Dicom\Network\Messages;

use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Dicom\CDicomStreamReader;
use Ox\Interop\Dicom\CDicomStreamWriter;
use Ox\Interop\Dicom\Network\CDicomMessage;

/**
 * A Presentation Data Value
 * 
 * @see DICOM Standard PS 11_08, section 9.3.5.1 and Annexe E
 */
class CDicomPDV implements IShortNameAutoloadable {
  
  /**
   * The length of the PDV
   * 
   * @var integer
   */
  protected $length = null;
  
  /**
   * The Presentation Context ID
   * 
   * @var integer
   */
  protected $pres_context_id = null;
  
  
  /**
   * The presentation contexts
   * 
   * @var array
   */
  protected $presentation_contexts = null;
  
  /**
   * The transfer syntax
   * 
   * @var string
   */
   protected $transfer_syntax = null;
  
  /**
   * The message control header
   * 
   * @var integer
   */
  protected $message_control_header = null;
  
  /**
   * The different values for the message control header and their signification
   * 
   * @var array
   */
  static $message_control_header_values = array(
    0 => "Data, not last fragment",
    1 => "Command, not last fragment",
    2 => "Data, last fragment",
    3 => "Command, last fragment"
  );
  
  /**
   * The message
   * 
   * @var CDicomMessage
   */
  protected $message = null;
  
  /**
   * The binary string of the pdv
   * 
   * @var string
   */
  protected $binary_content = null;
  
  /**
   * The constructor.
   * 
   * @param array $datas Default null. 
   * You can set all the field of the class by passing an array, the keys must be the name of the fields.
   */
  function __construct(array $datas = array()) {
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
   * Return the length
   * 
   * @return integer
   */
  function getLength() {
    return $this->length;
  }
  
  /**
   * Set the length
   * 
   * @param integer $length The length
   * 
   * @return null
   */
  function setLength($length) {
    $this->length = $length;
  }
  
  /**
   * Return the presentation context id
   * 
   * @return integer
   */
  function getPresContextId() {
    return $this->pres_context_id;
  }
  
  /**
   * Set the presentation context id
   * 
   * @param integer $id The presentation context id
   * 
   * @return null
   */
  function setPresContextId($id) {
    $this->pres_context_id = $id;
  }
  
  /**
   * Return the message control header
   * 
   * @return integer
   */
  function getMessageControlHeader() {
    return $this->message_control_header;
  }
  
  /**
   * Set the message control header
   * 
   * @param integer $header The message control header
   * 
   * @return null
   */
  function setMessageControlHeader($header) {
    $this->message_control_header = $header;
  }
  
  /**
   * Set the presentation contexts
   * 
   * @param array $presentation_contexts The presentation contexts
   * 
   * @return null
   */
  function setPresentationContexts($presentation_contexts) {
    $this->presentation_contexts = $presentation_contexts;
  }
  
  /**
   * Get the presentation contexts
   * 
   * @return array
   */
  function getPresentationContexts() {
    return $this->presentation_contexts;
  }
  
  /**
   * Set the transfer syntax
   * 
   * @param string $transfer_syntax The transfer syntax
   * 
   * @return null
   */
  function setTransferSyntax($transfer_syntax) {
    $this->transfer_syntax = $transfer_syntax;
  }
  
  /**
   * Get the transfer syntax.
   * If the transfer syntax is not set, get it from the presentation context
   * 
   * @return string
   */
  function getTransferSyntax() {
    if (!$this->transfer_syntax) {
      foreach ($this->presentation_contexts as $_pres_context) {
        if ($_pres_context->id == $this->pres_context_id) {
          $this->transfer_syntax = $_pres_context->transfer_syntax;
        }
      }
    }
    return $this->transfer_syntax;
  }
  
  /**
   * Return the message
   * 
   * @return CDicomMessage
   */
  function getMessage() {
    return $this->message;
  }
  
  /**
   * Set the binary content
   * 
   * @param string $content The content
   * 
   * @return null
   */
  function setBinaryContent($content) {
    $this->binary_content = $content;
  }
  
  /**
   * Get the binary content
   * 
   * @return string
   */
  function getBinaryContent() {
    return $this->binary_content;
  }
  
  /**
   * Set the message
   * 
   * @param array $datas Must contain 2 things, the type of the message, with the key "type",
   * and the messages datas, an array, with the key "datas"
   * 
   * @return null
   */
  function setMessage($datas) {
    $message_class = CDicomMessageFactory::getMessageClass($datas["type"]);  
    $this->message = new $message_class($datas["datas"]);
  }
  
  /**
   * Calculate the length of the pdv, without the field "length"
   * 
   * @param integer $message_length The length of the message
   * 
   * @return null
   */
  protected function calculateLength($message_length) {
    $this->length = 2 + $message_length;
  }
  
  /**
   * Return the total length of the pdv
   * 
   * @return integer 
   */
  function getTotalLength() {
    if (!$this->length && $this->message) {
      $this->calculateLength(strlen($this->message->getContent()));
    }
    return $this->length + 4;
  }
  
  /**
   * Encode the PDV
   * 
   * @param CDicomStreamWriter $stream_writer The stream writer
   * 
   * @return null
   */
  function encode(CDicomStreamWriter $stream_writer) {
    $handle = fopen("php://temp", "w+");
    $message_stream = new CDicomStreamwriter($handle);
    
    if (!$this->transfer_syntax = $this->getTransferSyntax()) {
      /** @todo throw exception **/
      return;
    }
    
    $this->message->encode($message_stream, $this->transfer_syntax);
    
    $this->calculateLength(strlen($message_stream->buf));
    
    $stream_writer->writeUInt32($this->length);
    $stream_writer->writeUInt8($this->pres_context_id);
    $stream_writer->writeUInt8($this->message_control_header);
    
    $stream_writer->write($message_stream->buf, strlen($message_stream->buf));
    $message_stream->close();
    $this->setBinaryContent($stream_writer->buf);
  }
  
  /**
   * Decode the PDV
   * 
   * @param CDicomStreamReader $stream_reader The stream reader
   * 
   * @return null
   */
  function decode(CDicomStreamReader $stream_reader) {
    // On fait un stream temp pour le message
    //$this->length = $stream_reader->readUInt32();
    $this->pres_context_id = $stream_reader->readUInt8();
    $this->message_control_header = $stream_reader->readUInt8();
    
    $message_length = $this->length - 2;
    $message_content = $stream_reader->read($message_length);
    
    $handle = fopen("php://temp", "w+");
    fwrite($handle, $message_content);

    $message_stream = new CDicomStreamReader($handle);
    $message_stream->rewind();
    $message_stream->setStreamLength($message_length);
    
    if (!$this->transfer_syntax = $this->getTransferSyntax()) {
      /** @todo throw exception **/
    }

    $this->message = CDicomMessageFactory::decodeMessage($message_stream, $this->message_control_header, $this->transfer_syntax);
    $message_stream->close();
    
    $content = substr($stream_reader->buf, 13) . $message_stream->buf;
    $this->setBinaryContent($content);
  }
  
  /**
   * Return a string representation of the class
   * 
   * @return string
   */
  function __toString() {
    $str = "<ul>
              <li>Length : $this->length</li>
              <li>Presentation context ID : $this->pres_context_id</li>
              <li>Message control header : ". self::$message_control_header_values[$this->message_control_header] . "</li>
              <li>" . $this->message->__toString() . "</li>
            </ul>";
    return $str;
  }
}