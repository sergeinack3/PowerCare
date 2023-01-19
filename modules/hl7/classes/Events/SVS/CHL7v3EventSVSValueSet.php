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
 * CHL7v3EventSVSValueSet
 * Retrieve value set
 */
class CHL7v3EventSVSValueSet implements IShortNameAutoloadable {
  public $id;
  public $version;
  public $displayName;

  /** @var array */
  public $concept_list = array();

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
    $this->id          = $dom->getValueAttributNode($elt, "id");
    $this->version     = $dom->getValueAttributNode($elt, "version");
    $this->displayName = $dom->getValueAttributNode($elt, "displayName");

    foreach ($dom->queryNodes($prefix."ConceptList", $elt) as $_concept_list) {
      $concept_list = new CHL7v3EventSVSConceptList($dom);
      $concept_list->bind($dom, $_concept_list, $prefix);

      $this->concept_list[] = $concept_list;
    }
  }
}