<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMNode;

/**
 * Structure d'un message HL7
 * 
 * Message
 * |- Segment              \n
 *   |- Field              |
 *     |- FieldItem        ~
 *       |- Component      ^
 *         |- Subcomponent &
 */
abstract class CHL7v2Entity extends CHL7v2 {
  protected static $_id = 0;
  protected $id;
  public $spec_filename;

  /** @var CHL7v2DOMDocument */
  public $specs;
  public $data;
  
  /**
   * Initializes an internal entity counter
   */
  function __construct(){
    $this->id = self::$_id++;
  }
  
  /**
   * Get the internal entity counter
   * 
   * @return integer The entity counter
   */
  function getId(){
    return $this->id;
  }
  
  /**
   * Saves the string message in the entity
   * 
   * @param string $data The string data
   * 
   * @return void
   */
  function parse($data) {
    $this->data = $data;
  }
  
  /**
   * Fill the current entity with structured data
   * 
   * @param array $items The structure
   * 
   * @return void|array
   */
  function fill($items) {
    
  }

  /**
   * Tells whether to keep the entity as is or not
   *
   * @return boolean Whether to keep the entity as is or not
   */
  function keep() {
    return in_array($this->name, $this->getMessage()->getKeepOriginal());
  }
  
  /**
   * Appends an error object in the errors array
   * 
   * @param integer      $code   The code of the error
   * @param string       $data   Additional info about the error
   * @param CHL7v2Entity $entity The entity where the error occurred
   * @param integer      $level  The error level : CHL7v2Error::E_ERROR or CHL7v2Error::E_WARNING
   * 
   * @return void
   */
  function error($code, $data, $entity = null, $level = CHL7v2Error::E_ERROR) {    
    $this->getMessage()->error($code, $data, $entity, $level);
  }
  
  /**
   * Validate the current entity
   * 
   * @return boolean Valid or not
   */
  abstract function validate();
  
  /**
   * Get the current entity's containing message
   * 
   * @return CHL7v2Message The containing message
   */
  abstract function getMessage();
  
  /**
   * Get the current entity's containing segment
   * 
   * @return CHL7v2Segment The containing segment
   */
  abstract function getSegment();
  
  /**
   * Get the current entity's path
   * 
   * @param string  $separator The separator to use in the path
   * @param boolean $with_name Put the name of the entities in the path
   * 
   * @return array The path of the current entity
   */
  abstract function getPath($separator = ".", $with_name = false);
  
  /**
   * Get the path as a string
   *
   * @param string  $glue      The glue between the parts of the path
   * @param string  $separator The separator of the path
   * @param boolean $with_name Put the name of the entities in the path
   * 
   * @return string The path as a string
   */
  function getPathString($glue = "/", $separator = ".", $with_name = true) {
    return implode($glue, $this->getPath($separator, $with_name));
  }
  
  /**
   * Get the encoding of the current message
   * 
   * @return string The encoding of the current message
   */
  function getEncoding() {
    return $this->getMessage()->getEncoding();
  }
  
  /**
   * Add the current entity in an XML node
   * 
   * @param DOMNode $node          The node to insert data into
   * @param boolean $hl7_datatypes Use the HL7 data formatting
   * @param string  $encoding      The XML document encoding
   * 
   * @return void
   */
  abstract function _toXML(DOMNode $node, $hl7_datatypes, $encoding);
}
