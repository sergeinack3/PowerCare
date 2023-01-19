<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMElement;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CMbXPath;

class CHL7v2DOMElement extends DOMElement implements IShortNameAutoloadable {
  /**
   * Get the valid elements to handle
   *
   * @return array A list of the node names to handle
   */
  function getValidPatterns(){
    return array("segment", "group");
  }

  /**
   * Reset the current element's counters
   *
   * @return void
   */
  function reset(){
    $this->setAttribute("mbOccurences", 0);
    $this->setAttribute("mbOpen", 0);
    //$this->setAttribute("mbEmpty", 0);
  }

  /**
   * Tells whether the current element is required
   *
   * @return boolean Required or not
   */
  function isRequired(){
    return (string)$this->getAttribute("minOccurs") !== "0";
  }

  /**
   * Tells whether the current element is unbounded (can occur more than once)
   *
   * @return boolean Unbounded or not
   */
  function isUnbounded(){
    return $this->getAttribute("maxOccurs") === "unbounded";
  }

  /**
   * Tells whether the current element is forbidden (should never appear)
   *
   * @return boolean Forbidden or not
   */
  function isForbidden(){
    return $this->getAttribute("forbidden") === "true";
  }

  /**
   * Obligé de mettre les prop de cadinalité et d'ouverture en attributs sinon ca créé des enfants
   *
   * @return void
   */
  private function init(){
    if (!$this->hasAttribute("mbOccurences")) {
      $this->setAttribute("mbOccurences", 0);
    }

    if (!$this->hasAttribute("mbOpen")) {
      $this->setAttribute("mbOpen", 0);
    }

    if (!$this->hasAttribute("mbEmpty")) {
      $this->setAttribute("mbEmpty", 0);
    }
  }

  /**
   * Found cardinality
   *
   * @return integer The number of occurences of the current node
   */
  function getOccurences(){
    return (int)$this->getAttribute("mbOccurences");
  }

  /**
   * If the current group is "open"
   *
   * @return boolean Open or not
   */
  function isOpen(){
    return (string)$this->getAttribute("mbOpen") == 1;
  }

  /**
   * If the group is "used"
   *
   * @return boolean Used or not
   */
  function isUsed(){
    return $this->getOccurences() > 0;
  }

  /**
   * If the group is "empty"
   *
   * @return boolean Empty or not
   */
  function isEmpty(){
    return (string)$this->getAttribute("mbEmpty") !== "0";
  }

  /**
   * Marks the element as "open" and all its children
   *
   * @return void
   */
  function markOpen(){
    $this->init();
    $this->setAttribute("mbOpen", 1);

    /** @var self $parent */
    $parent = $this->parentNode;

    if ($parent && $parent->nodeName !== "message") {
      $parent->markOpen();
    }
  }

  /**
   * Marks the current element as "used"
   *
   * @return void
   */
  function markUsed(){
    $this->init();
    $this->markOpen();
    $this->setAttribute("mbOccurences", $this->getOccurences()+1);
  }

  /**
   * Marks the current element as NOT "empty", and all its ancestors
   *
   * @return void
   */
  function markNotEmpty(){
    $this->init();
    $this->setAttribute("mbEmpty", 0);

    /** @var self $parent */
    $parent = $this->parentNode;

    if ($parent && $parent->nodeName !== "message") {
      $parent->markNotEmpty();
    }
  }

  /**
   * Marks the current element as "empty"
   *
   * @return void
   */
  function markEmpty(){
    $this->init();
    $this->setAttribute("mbEmpty", 1);
  }

  /**
   * Get the segment's header content as a string
   *
   * @return string|null The segment's header content as a string
   */
  function getSegmentHeader(){
    if ($this->nodeName === "segment") {
      return (string)$this->nodeValue;
    }
    
    return null;
  }

  /**
   * Get a quick view of the current element
   *
   * @return string The view
   */
  function state(){
    return "[occ:".$this->getOccurences().", ".
    "open:".($this->isOpen()?"true":"false").", ".
    "empty:".($this->isEmpty()?"true":"false")."]";
  }

  /**
   * @return self
   */
  function getParent(){
    return $this->parentNode;
  }

  /**
   * @return self
   */
  function getNextSibling(){
    return $this->nextSibling;
  }

  /**
   * @return self
   */
  function getFirstChild(){
    return $this->firstChild;
  }

  function getName(){
    return $this->nodeName;
  }

  function query($query) {
    $xpath = new CMbXPath($this->ownerDocument);
    return $xpath->query($query, $this);
  }

  function queryTextNode($query) {
    $xpath = new CMbXPath($this->ownerDocument);
    return $xpath->queryTextNode($query, $this);
  }
}