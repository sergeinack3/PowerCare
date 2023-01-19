<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMElement;
use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;

/**
 * HL7v2 Transformation
 */
class CHL7v2Transformation implements IShortNameAutoloadable {
  protected $message;
  protected $messageName;
  protected $messageNameXpath;

  /**
   * Construct
   *
   * @param string $version     Version
   * @param string $extension   Extension
   * @param string $messageName Message name
   */
  function __construct($version, $extension, $messageName, ?string $messageNameXpath = '') {
    $hl7v2 = new CHL7v2Message();
    $hl7v2->version = $version;
    $hl7v2->extension = $extension;

    $this->message          = $hl7v2;
    $this->messageName      = $messageName;
    $this->messageNameXpath = $messageNameXpath;
  }

  /**
   * Get segments
   *
   * @return array
   * @throws Exception
   */
  function getSegments() {
    $message_schema = $this->message->getSchema("message", $this->messageName);

    $xpath = new CHL7v2MessageXPath($message_schema);

    $segment = $xpath->queryUniqueNode("//segments");

    $tree = array();

    $this->getSegmentTree($segment, $tree);

    return $tree;
  }

  /**
   * Get segment tree
   *
   * @param DOMElement $element Element
   * @param array      &$tree   Tree
   *
   * @return void
   */
  function getSegmentTree(DOMElement $element, &$tree) {
    foreach ($element->childNodes as $_element) {
      /** @var DOMElement $_element */
      switch ($_element->nodeName) {
        case "segment":
          $segment_schema = $this->message->getSchema("segment", $_element->nodeValue);
          if ($segment_schema) {
              $xpath          = new CHL7v2MessageXPath($segment_schema);

              $tree[$_element->nodeValue] = array(
                  "type"        => $_element->nodeName,
                  "name"        => $_element->nodeValue,
                  "fullpath"    => $_element->getNodePath(),
                  "description" => $xpath->queryTextNode("description"),
                  "forbidden"   => $_element->getAttribute("forbidden") == "true"
              );
          }

          break;

        case "group":
          $subtree = array();
          $this->getSegmentTree($_element, $subtree);

          $group_name = $_element->getAttribute("name");

          $tree[$group_name] = array(
            "type"        => $_element->nodeName,
            "name"        => $group_name,
            "fullpath"    => $group_name,
            "description" => "",
            "children"    => $subtree,
            "forbidden"   => $_element->getAttribute("forbidden") == "true"
          );
          break;

        default:
      }
    }
  }

  /**
   * Get fields tree
   *
   * @param string $segment  Segment name
   * @param string $fullpath Full path segment
   *
   * @return array
   */
  function getFieldsTree($segment, string $fullpath) {
      // Récupération en amont pour avoir le chemin complet du segment en question
      $message_schema = $this->message->getSchema("message", $this->messageName);

      $xpath_message = new CHL7v2MessageXPath($message_schema);

      // Je remonte à tous les parents tant que le nodeName du parent retrouvé n'est pas "segments"
      $segments_found = $xpath_message->query($fullpath);

      $full_path_segment_parent = '';
      if ($segments_found && $segments_found->length == 1) {
          $segment_found = $segments_found->item(0);

          if ($segment_found) {
              $parent_node = $segment_found;
              do {
                  $parent_node = $parent_node->parentNode;
                  if ($parent_node->nodeName === 'group' && $parent_node->attributes->getNamedItem('name')->value) {
                      $full_path_segment_parent = $this->messageNameXpath.'.'.$parent_node->attributes->getNamedItem('name')->value . '/'. $full_path_segment_parent;
                  }
              } while ($parent_node->nodeName != 'segments');
          }
      }

    $segment_schema = $this->message->getSchema("segment", $segment);
    $xpath = new CHL7v2MessageXPath($segment_schema);

    $children = array();
    $fields = $xpath->query("//field");
    foreach ($fields as $_field) {
      $segment_name = $xpath->queryTextNode("name", $_field);
      $segment_datatype = $xpath->queryTextNode("datatype", $_field);

      $_fields = array();

      $field_schema = $this->message->getSchema("composite", $segment_datatype);
      $field_xpath = new CHL7v2MessageXPath($field_schema);

      $components = $field_xpath->query("//field");
      foreach ($components as $_component) {
        $component_name     = $field_xpath->queryTextNode("name"    , $_component);
        $component_datatype = $field_xpath->queryTextNode("datatype", $_component);

        $fullpath_component = "$full_path_segment_parent$segment/$segment_name/$component_name";

        $_datatypes = array();
        $this->readDataTypeSchema($_datatypes, $segment_datatype, $fullpath_component);

        $_fields[] = array(
          "name"      => $component_name,
          "fullpath"  => $fullpath_component,
          "forbidden" => $_field->getAttribute("forbidden") == "true",
          "datatype"  => $component_datatype,
          "children"  => $_datatypes
        );
      }

      $fullpath_segment = "$full_path_segment_parent$segment/$segment_name";

      $children[] = array(
        "name"      => $segment_name,
        "fullpath"  => $fullpath_segment,
        "forbidden" => $_field->getAttribute("forbidden") == "true",
        "datatype"  => $segment_datatype,
        "children"  => $_fields,
      );
    }

    $tree = array(
      "type"      => "segment",
      "name"      => $segment,
      "fullpath"  => "$full_path_segment_parent$segment",
      "children"  => $children,
    );

    return $tree;
  }

  /**
   * Get fields tree
   *
   * @param array  &$_datatypes        Datatypes
   * @param string $datatype_name      Datatype name
   * @param string $fullpath_component Fullpath
   *
   * @return array
   */
  function readDataTypeSchema(&$_datatypes, $datatype_name, $fullpath_component) {
    if (array_key_exists($datatype_name, CHDataType::$typesMap)) {
      return array();
    }

    $datatype_schema = $this->message->getSchema("composite", $datatype_name);

    $datatype_xpath  = new CHL7v2MessageXPath($datatype_schema);
    $fields          = $datatype_xpath->query("//field");

    foreach ($fields as $_field) {
      $_component_datatype = $datatype_xpath->queryTextNode("datatype", $_field);

      $children = array();
      $this->readDataTypeSchema($children, $_component_datatype, $fullpath_component);

      $_datatypes[] = array(
        "name"     => $_component_datatype,
        "fullpath" => "$fullpath_component/$_component_datatype",
        "datatype" => $_component_datatype,
        "children" => $children
      );
    }
  }
}
