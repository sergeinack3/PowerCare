<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMElement;
use DOMNodeList;
use Ox\Core\CMbXMLDocument;
use Ox\Core\CMbXPath;

/**
 * HL7v2 DOMDocument
 */
class CHL7v2DOMDocument extends CMbXMLDocument {
  /**
   * Query nodes by XPath
   *
   * @param string           $query       XPath
   * @param CHL7v2DOMElement $contextNode Context node
   *
   * @return CHL7v2DOMElement[]|DOMNodeList
   */
  function query($query, CHL7v2DOMElement $contextNode = null) {
    $xpath = new CMbXPath($this);

    return $xpath->query($query, $contextNode);
  }

  /**
   * Query text node
   *
   * @param string           $query       Query
   * @param CHL7v2DOMElement $contextNode Context
   *
   * @return string
   */
  function queryTextNode($query, CHL7v2DOMElement $contextNode = null) {
    $xpath = new CMbXPath($this);

    return $xpath->queryTextNode($query, $contextNode);
  }

  /**
   * Query unique node
   *
   * @param string           $query       Query
   * @param CHL7v2DOMElement $contextNode Context
   *
   * @return CHL7v2DOMElement|DOMElement
   * @throws \Exception
   */
  function queryUniqueNode($query, CHL7v2DOMElement $contextNode = null) {
    $xpath = new CMbXPath($this);

    return $xpath->queryUniqueNode($query, $contextNode);
  }

  /**
   * Get the element's items
   *
   * @return CHL7v2DOMElement[] The array of items
   */
  function getItems(){
    $items = array();
    foreach ($this->query("//field") as $field) {
      $items[] = $field;
    }
    return $items;
  }
}