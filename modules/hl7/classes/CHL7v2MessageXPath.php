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
 * Class CHL7v2MessageXML 
 * XPath HL7
 */
class CHL7v2MessageXPath extends CMbXPath {
  /**
   * Construct
   *
   * @param DOMDocument $dom DOM
   *
   * @retun CHL7v2MessageXPath
   */
  function __construct(DOMDocument $dom) {
    parent::__construct($dom);
    
    $this->registerNamespace("hl7", "urn:hl7-org:v2xml");
  }

  /**
   * Convert value
   *
   * @param string $value Value
   *
   * @return string
   */
  function convertEncoding($value) {
    return $value;
  }
}
