<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMNode;

class CHL7v2Field extends CHL7v2Entity {

  /** @var CHL7v2Segment */
  public $owner_segment;
  
  public $name;
  public $datatype;
  public $length;
  public $table;
  public $description;
  public $required;
  public $forbidden;
  public $unbounded;

    /** @var CHL7v2Entity[] */
  public $items         = array();


  /** @var CHL7v2DOMDocument */
  public $meta_spec;
  
  // private $_ts_fixed = false;

  /**
   * CHL7v2Field constructor.
   *
   * @param CHL7v2Segment    $segment Parent segment
   * @param CHL7v2DOMElement $spec    $this spec
   */
  function __construct(CHL7v2Segment $segment, CHL7v2DOMElement $spec) {
    parent::__construct();
    
    $this->owner_segment = $segment;
    $this->name     = $spec->queryTextNode("name");
    $this->datatype = $spec->queryTextNode("datatype");
    $this->length   = (int)$spec->getAttribute("length");
    $this->table    = (int)$spec->getAttribute("table");
    
    $this->meta_spec = $spec;
    
    /*if ($this->datatype == "TS") {
      $this->datatype = "DTM";
    }*/
    
    $this->description = $spec->queryTextNode("description");
    $this->required    = $spec->isRequired();
    $this->forbidden   = $spec->isForbidden();
    $this->unbounded   = $spec->isUnbounded();
  }

  /**
   * @inheritdoc
   */
  function _toXML(DOMNode $node, $hl7_datatypes, $encoding) {
      if (count($this->items) === 0) {
          $doc = $node->ownerDocument;
          $new_node = $doc->createElement($this->name);
          //$node->appendChild($new_node);
      }

    foreach ($this->items as $_item) {
      $_item->_toXML($node, $hl7_datatypes, $encoding);
    }
  }

  /**
   * Parses the field
   *
   * @param string $data Field string
   *
   * @return void
   */
  function parse($data) {
    parent::parse($data);
    
    if ($this->data === "" || $this->data === null) {
      // === $message->nullValue) { // nullValue ("") or null ??
      if ($this->required) {
        $this->error(CHL7v2Exception::FIELD_EMPTY, $this->getPathString(), $this);
      }
    }
    else {
      if ($this->forbidden) {
        $this->error(CHL7v2Exception::FIELD_FORBIDDEN, $this->data, $this);
      }
    }
    
    $message = $this->getMessage();
    
    $items = CHL7v2::split($message->repetitionSeparator, $this->data, $this->keep());
    
    /* // Ce test ne semble pas etre valide, car meme si maxOccurs n'est pas unbounded, 
    // on en trouve souvent plusieurs occurences 
    if (!$this->unbounded && count($items) > 1) {
      $this->error(CHL7v2Exception::TOO_MANY_FIELD_ITEMS, $this->name, $this);
    }*/
    
    $this->items = array();
    
    foreach ($items as $i => $components) {
      $_field_item = new CHL7v2FieldItem($this, $this->meta_spec, $i);
      $_field_item->parse($components);
      
      $this->items[] = $_field_item;
    }
    
    $this->validate();
  }

  /**
   * @inheritdoc
   */
  function fill($items) {
    if (!isset($items)) {
      return;
    }
    
    if (!is_array($items)) {
      $items = trim($items);
      $items = array($items);
    }
    
    $this->items = array();
    
    foreach ($items as $i => $data) {
      $_field_item = new CHL7v2FieldItem($this, $this->meta_spec, $i);
      $_field_item->fill($data);
      
      $this->items[] = $_field_item;
    }
  }

  /**
   * @inheritdoc
   */
  function validate() {
    foreach ($this->items as $item) {
      $item->validate();
    }
  }

  /**
   * @inheritdoc
   */
  function getSpecs() {
    $specs = $this->getMessage()->getSchema(self::PREFIX_COMPOSITE_NAME, $this->datatype, $this->getMessage()->extension);
    
    // The timestamp case, where Time contains TimeStamp data
    /*if (!$this->_ts_fixed && $this->datatype === "TS") {
      $specs->elements->field[0]->datatype = "DTM";
    }
    
    $this->_ts_fixed = true;*/
    
    return $specs;
  }

  /**
   * @inheritdoc
   */
  function getVersion() {
    return $this->owner_segment->getVersion();
  }

  /**
   * @inheritdoc
   */
  function getSegment() {
    return $this->owner_segment;
  }

  /**
   * @inheritdoc
   */
  function getMessage() {
    return $this->owner_segment->getMessage();
  }

  /**
   * @inheritdoc
   */
  function getPath($separator = ".", $with_name = false) {
    if ($with_name) {
      return array($this->name);
    }
    
    $self_pos = explode($separator, $this->name);
    return array((int)$self_pos[1]);
  }
  
  /**
   * Build a view of the type of the field
   * 
   * @return string The view of the form DATATYPE[LENGTH]
   */
  function getTypeTitle() {
    $str = $this->datatype;
    
    if ($this->length) {
      $str .= "[$this->length]";
    }
    
    return $str;
  }

  /**
   * To String magic method
   * 
   * @return string
   */
  function __toString() {
    $rs = $this->getMessage()->repetitionSeparator;
    
    if (CHL7v2Message::$decorateToString) {
      $rs = "<span class='rs'>$rs</span>";
    }
    
    if (empty($this->items)) {
      $item = new CHL7v2FieldItem($this, $this->meta_spec, 0);
      $items = array($item);
    }
    else {
      $items = $this->items;
    }
    
    $str = implode($rs, $items);
    
    if (CHL7v2Message::$decorateToString) {
      $str = "<span class='entity field' id='entity-er7-$this->id'>$str</span>";
    }
    
    return $str;
  }

  /**
   * Get segment struct
   *
   * @return array
   */
  function getStruct() {
    $data = array();

    foreach ($this->items as $_item) {
      $data[] = $_item->getStruct();
    }

    return $data;
  }
}
