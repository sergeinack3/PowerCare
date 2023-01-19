<?php
/**
 * @package Mediboard\Core
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core;

use Ox\Core\Autoload\IShortNameAutoloadable;
use SimpleXMLElement;

if (!class_exists(SimpleXMLElement::class, false)) {
  return;
}

class CMbSimpleXMLElement extends SimpleXMLElement implements IShortNameAutoloadable {
  function getValidPatterns(){
    return array("*");
  }
  
  function getXpath($prefix = "") {
    $tokens = array();
    
    foreach ($this->getValidPatterns() as $patt) {
      $tokens[] = "$prefix$patt";
    }
    
    return implode(" | ", $tokens);
  }
  
  /**
   * @return CMbSimpleXMLElement
   */
  function getParent(){
    return current($this->xpath($this->getXpath("parent::")));
  }
  
  /**
   * @return CMbSimpleXMLElement
   */
  function getNextSibling(){
    return current($this->xpath($this->getXpath("following-sibling::")));
  }
  
  /**
   * @return CMbSimpleXMLElement
   */
  function getPreviousSibling(){
    return current($this->xpath($this->getXpath("preceding-sibling::")));
  }
  
  /**
   * @return CMbSimpleXMLElement
   */
  function getFirstChild(){
    return current($this->xpath($this->getXpath()));
  }
  
  /**
   * @return CMbSimpleXMLElement
   */
  function getNext(){
    if ($next = $this->getNextSibling()) {
      return $next;
    }
    
    if ($parent = $this->getParent()) {
      return $parent->getNext();
    }
  }
}
