<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7\Events\SVS;

use DOMElement;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Interop\Hl7\CHL7v3MessageXML;

/**
 * CHL7v3EventSVSConceptList
 * Retrieve concept list
 */
class CHL7v3EventSVSConceptList implements IShortNameAutoloadable {
  public $lang;

    /** @var array */
  public $concept = array();

  /**
   * Bind value set
   *
   * @param CHL7v3MessageXML $dom    Document
   * @param DOMElement       $elt    Element
   * @param string           $prefix Prefix
   *
   * @return void
   */
  function bind(CHL7v3MessageXML $dom, DOMElement $elt, $prefix) {
    $this->lang = $dom->getValueAttributNode($elt, "xml:lang");

    foreach ($dom->queryNodes($prefix."Concept", $elt) as $_concept) {
      $concept = new CHL7v3EventSVSConcept($dom);
      $concept->bind($dom, $_concept);

      $this->concept[] = $concept;
    }
  }
}