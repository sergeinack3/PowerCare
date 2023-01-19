<?php
/**
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Hl7;
use DOMDocument;
use Ox\Core\CMbXPath;

/**
 * Class CHL7v3MessageXPath
 * XPath HL7v3
 */
class CHL7v3MessageXPath extends CMbXPath {
  /**
   * Construct
   *
   * @param DOMDocument $dom DOM
   *
   * @retun CHL7v2MessageXPath
   */
  function __construct(DOMDocument $dom) {
    parent::__construct($dom);
    
    $this->registerNamespace("hl7", "urn:hl7-org:v3");
    $this->registerNamespace("svs", "urn:ihe:iti:svs:2008");
  }
}
