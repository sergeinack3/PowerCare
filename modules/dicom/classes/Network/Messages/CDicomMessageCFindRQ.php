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
use Ox\Interop\Dicom\Data\CDicomDataSet;

/**
 * The C-Find-RQ message
 * 
 * @see DICOM Standard PS 3.07, section 9.1.2 ans 9.3.2.1
 */
class CDicomMessageCFindRQ implements IShortNameAutoloadable {
  
  /**
   * The length of the group
   * 
   * @var CDicomDataSet
   */
  protected $command_group_length = null;
  
  /**
   * The affected SOP class UID.
   * 
   * @var CDicomDataSet
   */
  protected $affected_sop_class = null;
  
  /**
   * Identify the DIMSE-C operation, here C-Find-RQ
   * 
   * @var CDicomDataSet
   */
  protected $command_field = null;
  
  /**
   * Help to distinguishe this message from others
   * 
   * @var CDicomDataSet
   */
  protected $message_id = null;
  
  /**
   * The priority of the request.
   * The value should be equal to 0x0002 (low), 0x0000 (medium) or 0x0001 (high)
   * 
   * @var CDicomDataSet
   */
  protected $priority = null;
  
  /**
   * Indicates that no data sets are present in the message
   * 
   * @var CDicomDataSet
   */
  protected $command_data_set = null;
  
  /**
   * The encoded content of the message
   * 
   * @var string
   */
  protected $content = null;
  
  /**
   * The type of the message
   * 
   * @var string
   */
  public $type = "C-Find-RQ";
  
  static $type_int = 0x0020; 
  
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
   * Get the command group length data set
   * 
   * @return CDicomDataSet
   */
  function getCommandGroupLength() {
    return $this->command_group_length;
  }
  
  /**
   * Set the command group length data set
   * 
   * @param integer $length The length
   * 
   * @return null
   */
  function setCommandGroupLength($length) {
    if ($length) {
      $this->command_group_length = new CDicomDataSet(array("group_number" => 0x0000, "element_number" => 0x0000, "value" => $length));
    }
  }
  
  /**
   * Get the affected SOP class data set
   * 
   * @return CDicomDataSet
   */
  function getAffectedSopClass() {
    return $this->affected_sop_class;
  }
  
  /**
   * Set the affected SOP class data set
   * 
   * @return null
   */
  function setAffectedSopClass() {
    $this->affected_sop_class = new CDicomDataSet(array("group_number" => 0x0000, "element_number" => 0x0002, "value" => "1.2.840.10008.5.1.4.31"));
  }
  
  /**
   * Get the command field data set
   * 
   * @return CDicomDataSet
   */
  function getCommandField() {
    return $this->command_field;
  }
  
  /**
   * Set the command field data set
   * 
   * @return null
   */
  function setCommandField() {
    $this->command_field = new CDicomDataSet(array("group_number" => 0x0000, "element_number" => 0x0100, "value" => 0x0020));
  }
  
  /**
   * Get the message id data set
   * 
   * @return CDicomDataSet
   */
  function getMessageId() {
    return $this->message_id;
  }
  
  /**
   * Set the message id data set
   * 
   * @param integer $id The message id
   * 
   * @return null
   */
  function setMessageId($id) {
    $this->message_id = new CDicomDataSet(array("group_number" => 0x0000, "element_number" => 0x0110, "value" => $id));
  }
  
  /**
   * Get the priority of the message
   * 
   * @return CDicomDataSet
   */
  function getPriority() {
    return $this->priority;
  }
  
  /**
   * Set the priority of the request
   * 
   * @param integer $priority the priority
   * 
   * @return null
   */
  function setPriority($priority) {
    $this->priority = new CDicomDataSet(array("group_number" => 0x0000, "element_number" => 0x0700, "value" => $priority));
  }
  
  /**
   * Get the command data set type
   * 
   * @return CDicomDataSet
   */
  function getCommandDataSet() {
    return $this->command_data_set;
  }
  
  /**
   * Set the command data set type
   * 
   * @param integer $command_data_set An hexadecimal number. If data is present, should be equal to any hexadecimal number, and if not, to 0x0101.
   * 
   * @return null
   */
  function setCommandDataSet($command_data_set) {
    $this->command_data_set = new CDicomDataSet(array("group_number" => 0x0000, "element_number" => 0x0800, "value" => $command_data_set));
  }
  
  /**
   * Return the encoded content
   * 
   * @return string
   */
  function getContent() {
    return $this->content;
  }
  
  /**
   * Set the encoded content
   * 
   * @param string $content The content
   * 
   * @return string
   */
  function setContent($content) {
    $this->content = $content;
  }
  
  /**
   * Encode the message
   * 
   * @param CDicomStreamWriter $stream_writer   The stream writer
   * 
   * @param string             $transfer_syntax The UID of the transfer syntax
   * 
   * @return null
   */
  function encode(CDicomStreamWriter $stream_writer, $transfer_syntax) {
    $handle = fopen("php://temp", "w+");
    $group_stream = new CDicomStreamwriter($handle);
    
    $this->setAffectedSopClass();
    $this->setCommandField();
    
    $this->affected_sop_class->encode($group_stream, $transfer_syntax);
    $this->command_field->encode($group_stream, $transfer_syntax);
    $this->message_id->encode($group_stream, $transfer_syntax);
    $this->priority->encode($group_stream, $transfer_syntax);
    $this->command_data_set->encode($group_stream, $transfer_syntax);
    
    $group_length = strlen($group_stream->buf);
    $this->setCommandGroupLength($group_length);
    
    $this->command_group_length->encode($stream_writer, $transfer_syntax);
    
    $this->setContent($group_stream->buf);
    $stream_writer->write($group_stream->buf);
    
    $group_stream->close();
  }
  
  /**
   * Decode the message
   * 
   * @param CDicomStreamReader $stream_reader   The stream reader
   * 
   * @param string             $transfer_syntax The UID of the transfer syntax
   * 
   * @return null
   */
  function decode(CDicomStreamReader $stream_reader, $transfer_syntax) {
    $this->command_group_length = new CDicomDataSet();
    $this->command_group_length->decode($stream_reader, $transfer_syntax);
    
    $this->affected_sop_class = new CDicomDataSet();
    $this->affected_sop_class->decode($stream_reader, $transfer_syntax);
    
    $this->command_field = new CDicomDataSet();
    $this->command_field->decode($stream_reader, $transfer_syntax);
    
    $this->message_id = new CDicomDataSet();
    $this->message_id->decode($stream_reader, $transfer_syntax);
    
    $this->priority = new CDicomDataSet();
    $this->priority->decode($stream_reader, $transfer_syntax);
    
    $this->command_data_set = new CDicomDataSet();
    $this->command_data_set->decode($stream_reader, $transfer_syntax);
  }
  
  /**
   * Check if the message is well formed
   * 
   * @return boolean
   */
  function isWellFormed() {
    if ($this->command_group_length->getGroupNumber() != 0x0000 || $this->command_group_length->getElementNumber() != 0x0000) {
      return false;
    }
    
    if ($this->affected_sop_class->getGroupNumber() != 0x0000 || $this->affected_sop_class->getElementNumber() != 0x0002) {
      return false;
    }
    
    if ($this->command_field->getGroupNumber() != 0x0000 || $this->command_field->getElementNumber() != 0x0100) {
      return false;
    }
    
    if ($this->message_id->getGroupNumber() != 0x0000 || $this->message_id->getElementNumber() != 0x0110) {
      return false;
    }
    
    if ($this->priority->getGroupNumber() != 0x0000 || $this->priority->getElementNumber() != 0x0700) {
      return false;
    }
    
    if ($this->command_data_set->getGroupNumber() != 0x0000 || $this->command_data_set->getElementNumber() != 0x0800) {
      return false;
    }
    return true;
  }
  
  /**
   * Return a string representation of the class
   * 
   * @return string
   */
  function __toString() {
    return "<table>
              <tr>
                <th>Tag</th><th>Name</th><th>VR</th><th>Length</th><th>Value</th>
              </tr>
              <tr>" . $this->command_group_length->__toString() . "</tr>
              <tr>" . $this->affected_sop_class->__toString() . "</tr>
              <tr>" . $this->command_field->__toString() . "</tr>
              <tr>" . $this->message_id->__toString() . "</tr>              
              <tr>" . $this->priority->__toString() . "</tr>
              <tr>" . $this->command_data_set->__toString() . "</tr>
            </table>";
  }
}