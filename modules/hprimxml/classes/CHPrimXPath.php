<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use DOMDocument;
use DOMNode;
use Ox\Core\CMbXPath;

/**
 * Class CHPrimXPath
 */
class CHPrimXPath extends CMbXPath {
  /**
   * @see parent::__construct
   */
  function __construct(DOMDocument $doc) {
    parent::__construct($doc);
    
    $this->registerNamespace("hprim", "http://www.hprim.org/hprimXML");
  }

  /**
   * @see parent::nodePath
   */
  function nodePath(DOMNode $node) {    
    $name = "hprim:$node->nodeName";
    while (($node = $node->parentNode) && ($node->nodeName != "#document")) {
      $name = "hprim:$node->nodeName/$name";
    }
    
    return "'/$name'";
  }

  /**
   * @see parent::queryTextNode
   */
  function queryTextNode($query, DOMNode $contextNode = null, $purgeChars = "", $addslashes = false) {
    $text = "";
    if ($node = $this->queryUniqueNode($query, $contextNode)) {
      $text = utf8_decode($node->textContent);
      $text = str_replace(str_split($purgeChars), "", $text);
      $text = trim($text);
      if ($addslashes) {
        $text = addslashes($text);
      }
    }

    return $text;
  }

  /**
   * @see parent::getMultipleTextNodes
   */
  function getMultipleTextNodes($query, DOMNode $contextNode = null, $implode = false) {
    $array = array();
    $query = utf8_encode($query);
    $nodeList = $contextNode ? parent::query($query, $contextNode) : parent::query($query);
    
    foreach ($nodeList as $n) {
      $array[] = utf8_decode($n->nodeValue);
    }
    return $implode ? implode("\n", $array) : $array;
  }
}
