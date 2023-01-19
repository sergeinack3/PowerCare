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
 * HL7 field item
 */
class CHL7v2FieldItem extends CHL7v2Component {
  static $_get_path_full = false;
  

  /** @var CHL7v2Field */
  public $parent;

  /**
   * CHL7v2FieldItem constructor.
   *
   * @param CHL7v2Field      $field    Parent field
   * @param CHL7v2DOMElement $specs    $this specs
   * @param int              $self_pos Position of $this in the field
   */
  function __construct(CHL7v2Field $field, CHL7v2DOMElement $specs, $self_pos) {
    $message = $field->getMessage();
    
    $separators = array(
      // sub parts separator                 self type        sub part separator class
      array($message->componentSeparator,    "field-item",    "cs"), 
      array($message->subcomponentSeparator, "component",     "scs"),
      array(null,                            "sub-component", null),
    );
    
    parent::__construct($field, $specs, $self_pos, $separators);
  }

  /**
   * @inheritdoc
   */
  function getField() {
    return $this->parent;
  }

  /**
   * @inheritdoc
   */
  function _toXML(DOMNode $node, $hl7_datatypes, $encoding) {
    $doc = $node->ownerDocument;
    $field = $this->getField();
    $new_node = $doc->createElement($field->name);
    
    parent::_toXML($new_node, $hl7_datatypes, $encoding);
    
    $node->appendChild($new_node);
  }

  /**
   * @inheritdoc
   */
  function getPath($separator = ".", $with_name = false) {
    $path = $this->parent->getPath($separator, $with_name);
    
    if (self::$_get_path_full) {
      $path[] = $this->self_pos+1;
    }
    else {
      $path[count($path)-1] = $path[count($path)-1]."[".($this->self_pos+1)."]";
    }
   
    return $path;
  }
}
