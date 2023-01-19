<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMNode;

class CHL7v2SegmentGroup extends CHL7v2Entity {
  /** @var CHL7v2SegmentGroup[] */
  public $children = array();
  
  public $name;

  /** @var CHL7v2SegmentGroup */
  public $parent;

  /**
   * CHL7v2SegmentGroup constructor.
   *
   * @param CHL7v2SegmentGroup $parent     Parent group
   * @param CHL7v2DOMElement   $self_specs $this specs
   */
  function __construct(CHL7v2SegmentGroup $parent, CHL7v2DOMElement $self_specs) {
    parent::__construct();
    
    $this->parent = $parent;
    
    $this->specs = $self_specs;
    
    $name = $self_specs->getAttribute("name");

    $segments = $self_specs->query(".//segment");
    $values = array();
    foreach ($segments as $_segment) {
      $values[] = $_segment->nodeValue;
    }

    $this->name = ($name ?: implode(" ", $values));
    
    $parent->appendChild($this);
  }

  /**
   * @inheritdoc
   */
  function _toXML(DOMNode $node, $hl7_datatypes, $encoding) {
    $doc = $node->ownerDocument;
    $name = str_replace(" ", "_", $this->name);
    $new_node = $doc->createElement("{$doc->documentElement->nodeName}.$name");
    
    foreach ($this->children as $_child) {
      $_child->_toXML($new_node, $hl7_datatypes, $encoding);
    }
    
    $node->appendChild($new_node);
  }

  /**
   * Validate each children
   * 
   * @return void
   */
  function validate() {
    foreach ($this->children as $child) {
      $child->validate();
    }
  }

  /**
   * @inheritdoc
   */
  function getVersion(){
    return $this->parent->getVersion();
  }

  /**
   * @inheritdoc
   */
  function getSpecs(){
    return $this->specs;
  }

  /**
   * Get parent
   * 
   * @return CHL7v2SegmentGroup
   */
  function getParent() {
    return $this->parent;
  }

  /**
   * @inheritdoc
   */
  function getPath($separator = ".", $with_name = false){
    
  }
  
  /**
   * @inheritdoc
   */
  function getMessage() {
    return $this->parent->getMessage();
  }
  
  /**
   * @inheritdoc
   */
  function getSegment(){
    // N/A
  }

  /**
   * Append child
   * 
   * @param CHL7v2SegmentGroup $child Append child
   *
   * @return CHL7v2SegmentGroup
   */
  function appendChild($child){
    return $this->children[] = $child;
  }

  /**
   * Purge empty groups
   * 
   * @return void
   */
  function purgeEmptyGroups(){
    foreach ($this->children as $i => $child) {
      if (!$child instanceof CHL7v2SegmentGroup) {
        continue;
      }
      
      $child->purgeEmptyGroups();
      
      if ($child->isEmpty()) {
        unset($this->children[$i]);
      }
    }
  }

  /**
   * Tells if the group is empty
   * 
   * @return bool
   */
  function isEmpty(){
    foreach ($this->children as $child) {
      if (!$child instanceof CHL7v2SegmentGroup) {
        return false;
      }
      
      if (empty($child->children)) {
        return true;
      }
      
      if (!$child->isEmpty()) {
        return false;
      }
    }
    
    return true;
  }

  /**
   * Magic To String Method
   * 
   * @return string
   */
  function __toString(){
    $str = implode("", $this->children);
    
    if (CHL7v2Message::$decorateToString && !$this instanceof CHL7v2Message) {
      $str = "<div class='entity_foo group_bar' id='entity-er7-$this->id' data-title='$this->name'>$str</div>";
    }
    
    return $str;
  }
}
