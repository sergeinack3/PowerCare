<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMNode;
use Ox\Core\CMbArray;
use Ox\Core\CMbString;
use Ox\Interop\Eai\CInteropReceiver;

/**
 * A CHL7v2Component is an item of a composite value
 */
class CHL7v2Component extends CHL7v2Entity {

  /** @var CHL7v2Component */
  public $parent;

  public $children = array();


  /** @var string */
  public $data;

  /**
   * Separator used in $this->parse()
   * @var string
   */
  public $separator;

  /**
   * Component's max length
   * @var integer
   */
  public $length;

  /**
   * Data type
   * @var string
   */
  public $datatype;

  /**
   * Description
   * @var string
   */
  public $description;

  /**
   * Description
   * @var string
   */
  public $value_description;

  /**
   * Table
   * @var integer
   */
  public $table;

  /**
   * Position of self in its parent
   * @var integer
   */
  public $self_pos;


  /** @var CHL7v2DataType */
  public $props;

  public $invalid = false;

  function __construct(CHL7v2Entity $parent, CHL7v2DOMElement $specs, $self_pos, $separators) {
    parent::__construct();

    $this->parent = $parent;

    // Separators stack
    $this->separator = array_shift($separators);
    $this->separators = $separators;

    // Intrinsic properties
    $this->length      = (int)$specs->getAttribute("length");
    $this->table       = (int)$specs->getAttribute("table");
    $this->datatype    = $specs->queryTextNode("datatype");
    $this->description = $specs->queryTextNode("description");
    $this->self_pos    = $self_pos;
  }

  /**
   * @return CHL7v2DataType
   */
  function getProps(){
    if (!$this->props) {
      $this->props = $this->getMessage()->loadDataType($this->datatype);
    }

    return $this->props;
  }

  /**
   * Insert the current component inside an XML node
   *
   * @param DOMNode $node          The node to insert the data into
   * @param boolean $hl7_datatypes Format the values to HL7 or to Mediboard
   * @param boolean $encoding      The encoding of the XML document
   *
   * @return void
   */
  function _toXML(DOMNode $node, $hl7_datatypes, $encoding) {
    $doc = $node->ownerDocument;
    $field = $this->getField();

    if ($this->getProps() instanceof CHL7v2DataTypeComposite) {
      foreach ($this->children as $i => $_child) {
        $new_node = $doc->createElement("$this->datatype.".($i+1));
        $_child->_toXML($new_node, $hl7_datatypes, $encoding);
        $node->appendChild($new_node);
      }
    }
    else {
      if ($this->datatype === "TS" && $this->getProps() instanceof CHL7v2DataTypeDateTime) {
        $new_node = $doc->createElement("$this->datatype.1");
        $node->appendChild($new_node);
        $node = $new_node;
      }

      $str = $this->data;

      if (!$hl7_datatypes && $str !== "") {
        $str = $this->getProps()->toMB($str, $field);
      }

      /*$data_encoding = $this->getEncoding();

      if ($data_encoding && $data_encoding !== $encoding) {
        $str = mb_convert_encoding($str, $encoding, $data_encoding);
      }*/

      $new_node = $doc->createTextNode($str);
      $node->appendChild($new_node);
    }
  }

  /**
   * Parse a field item into components
   *
   * @param string $data The data to parse
   *
   * @return void
   */
  function parse($data) {
    parent::parse($data);

    $keep_original = $this->getField()->keep();

    // Is composite
    if (isset($this->separator[0]) && $this->getProps() instanceof CHL7v2DataTypeComposite) {
      $parts = CHL7v2::split($this->separator[0], $data, $keep_original);

      $component_specs = $this->getSpecs()->getItems();
      foreach ($component_specs as $i => $_component_spec) {
        if (array_key_exists($i, $parts)) {
          $_comp = new CHL7v2Component($this, $_component_spec, $i, $this->separators);
          $_comp->parse($parts[$i]);

          $this->children[] = $_comp;
        }
        elseif ($_component_spec->isRequired()) {
          $this->error(CHL7v2Exception::FIELD_EMPTY, $this->getPathString(), $this);
        }
      }
    }

    // Scalar type (NM, ST, ID, etc)
    else {
      $this->data = $this->getMessage()->unescape($this->data);
    }
  }

  /**
   * Fill a field item with data
   *
   * @param array $data The data to add to the current component
   *
   * @return void
   */
  function fill($data) {
    // Is composite
    if ($this->getProps() instanceof CHL7v2DataTypeComposite) {
      if (!is_array($data)) {
        $data = array($data);
      }

      $component_specs = $this->getSpecs()->getItems();
      foreach ($component_specs as $i => $_component_spec) {
        if (array_key_exists($i, $data)) {
          $_comp = new CHL7v2Component($this, $_component_spec, $i, $this->separators);
          $_comp->fill($data[$i]);

          $this->children[] = $_comp;
        }
        elseif ($_component_spec->isRequired()) {
          $this->error(CHL7v2Exception::FIELD_EMPTY, $this->getPathString(), $this);
        }
      }
    }

    // Scalar type (NM, ST, ID, etc)
    else {
      if (is_array($data)) {
        $this->error(CHL7v2Exception::INVALID_DATA_FORMAT, var_export($data, true), $this);
        return;
      }

      $prop = $this->getProps()->toHL7($data, $this->getField());
      $this->data = trim($prop ?? '');
    }
  }

  /**
   * Get the data type object
   *
   * @return CHL7v2DataType
   */
  function getSpecs(){
    return $this->getMessage()->getSchema(self::PREFIX_COMPOSITE_NAME, $this->datatype, $this->getMessage()->extension);
  }

  /**
   * Validate data in the field
   *
   * @return bool true if the component is valid
   */
  function validate(){
    $props   = $this->getProps();
    $message = $this->getMessage();

    if ($props instanceof CHL7v2DataTypeComposite) {
      foreach ($this->children as $child) {
        if (!$child->validate()) {
          $this->invalid = true;
        }
      }
    }
    else {
      // length
      $length = strlen($this->data);
      if ($this->length && $length > $this->length) {
        // Pour un receiver on va chercher la configuration sur le destinataire
        if ($message->actor && $message->actor instanceof CInteropReceiver &&
          CMbArray::get($message->actor->_configs, "check_field_length")) {
          $this->error(CHL7v2Exception::DATA_TOO_LONG, var_export($this->data, true)." ($length / $this->length)", $this);
          $this->invalid = true;
        }
      }

      // table
      if ($this->table && $this->data !== "") {
        $entries = CHL7v2::getTable($this->table, false, true);

        if (!empty($entries)) {
          if (!array_key_exists($this->data, $entries)) {
            $this->error(
              CHL7v2Exception::UNKNOWN_TABLE_ENTRY,
              "'$this->data' (table $this->table)",
              $this,
              CHL7v2Error::E_WARNING
            );

            $this->invalid = true;
          }
          else {
            $this->value_description = $entries[$this->data];
          }
        }
      }

      if (!$props->validate($this->data, $this->getField())) {
        //$this->error(CHL7v2Exception::INVALID_DATA_FORMAT, $this->data, $this);
        $this->invalid = true;
        return false;
      }
    }

    return true;
  }

  /**
   * Get the current field
   *
   * @return CHL7v2Field The field
   */
  function getField(){
    return $this->parent->getField();
  }

  /**
   * Get the current segment
   *
   * @return CHL7v2Segment The segment
   */
  function getSegment(){
    return $this->parent->getSegment();
  }

  /**
   * Get the current message
   *
   * @return CHL7v2Message The message
   */
  function getMessage(){
    return $this->parent->getMessage();
  }

  /**
   * Get the version number
   *
   * @return string the version number
   */
  function getVersion(){
    return $this->getMessage()->getVersion();
  }

  function getPath($separator = ".", $with_name = false){
    $path = $this->parent->getPath($separator, $with_name);
    $label = $this->self_pos+1;

    if ($with_name) {
      $label = "$this->datatype.$label";
    }

    $path[] = $label;
    return $path;
  }

  function getTypeTitle(){
    $str = $this->datatype;

    if ($this->length) {
      $str .= "[$this->length]";
    }

    return $str;
  }

  function __toString(){
    $field = $this->getField();

    if ($this->getProps() instanceof CHL7v2DataTypeComposite) {
      $sep = $this->separator[0];

      if (CHL7v2Message::$decorateToString) {
        $sep = "<span class='{$this->separator[2]}'>$sep</span>";
      }

      $str = implode($sep, $this->children);
    }
    else {
      if ($field->keep()) {
        $str = $this->data;
      }
      else {
        $str = $this->getMessage()->escape($this->data);
      }

      if (CHL7v2Message::$decorateToString) {
        $str = CMbString::htmlEntities($str);
      }
    }

    if (CHL7v2Message::$decorateToString) {
      $title = $field->owner_segment->name.".".$this->getPathString(".")." - $this->datatype - $this->description";

      if ($this->table != 0) {
        $value_description = $this->value_description ? $this->value_description : "?";
        $title .= " [$this->table => $value_description]";
      }

      $title = CMbString::htmlEntities($title);

      $xpath = ($this->getSegment()->name)."/".$this->getPathString("/", ".", true);

      $invalid = $this->invalid    ? 'invalid' : '';
      $table   = $this->table != 0 ? 'table-entry' : '';

      $str = "<span class='entity {$this->separator[1]} $invalid $table' id='entity-er7-$this->id'
                    data-title='$title' data-xpath='$xpath'>$str</span>";
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

    if (empty($this->children)) {
      return $this->data;
    }

    foreach ($this->children as $_children) {
      $data[] = $_children->getStruct();
    }

    return $data;
  }
}
