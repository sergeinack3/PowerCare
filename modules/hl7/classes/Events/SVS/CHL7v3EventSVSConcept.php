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
 * CHL7v3EventSVSConcept
 * Retrieve concept
 */
class CHL7v3EventSVSConcept implements IShortNameAutoloadable {
  public $displayName;
  public $codeSystem;
  public $code;

  /**
   * Bind value set
   *
   * @param CHL7v3MessageXML $dom Document
   * @param DOMElement       $elt Element
   *
   * @return void
   */
  function bind(CHL7v3MessageXML $dom, DOMElement $elt) {
    $this->displayName = $dom->getValueAttributNode($elt, "displayName");
    $this->codeSystem  = $dom->getValueAttributNode($elt, "codeSystem");
    $this->code        = $dom->getValueAttributNode($elt, "code");
  }
}