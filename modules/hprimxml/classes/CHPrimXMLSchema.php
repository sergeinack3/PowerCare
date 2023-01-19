<?php
/**
 * @package Mediboard\Hprimxml
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hprimxml;

use DOMXPath;
use Ox\Core\CMbXMLSchema;

/**
 * Class CHPrimXMLSchema
 */
class CHPrimXMLSchema extends CMbXMLSchema {
  /**
   * @see parent::__construct
   */
  function __construct() {
    parent::__construct();
    
    $root = $this->addElement($this, "xsd:schema", null, "http://www.w3.org/2001/XMLSchema");
    $this->addAttribute($root, "xmlns", "http://www.hprim.org/hprimXML");
    $this->addAttribute($root, "xmlns:insee", "http://www.hprim.org/inseeXML");
    $this->addAttribute($root, "targetNamespace", "http://www.hprim.org/hprimXML");
    $this->addAttribute($root, "elementFormDefault", "qualified");
    $this->addAttribute($root, "attributeFormDefault", "unqualified");
  }

  /**
   * @see parent::purgeImportedNamespaces
   */
  function purgeImportedNamespaces() {
    $xpath = new domXPath($this);
    foreach ($xpath->query('//*[@type]') as $node) {
      $matches = null;
      if (preg_match("/insee:(.*)/", $node->getAttribute("type"), $matches)) {
        $node->setAttribute("type", $matches[1]);
      }
    }
  }
}


