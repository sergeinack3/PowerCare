<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir;
use DOMDocument;
use DOMNode;
use Exception;
use Ox\Core\CMbXPath;

/**
 * XPath FHIR
 */
class CFHIRXPath extends CMbXPath {

    /** @var string */
    public const FHIR_NAMESPACE = 'http://hl7.org/fhir';

  /**
   * Construct
   *
   * @param DOMDocument $dom DOM
   *
   * @retun CFHIRXPath
   */
  function __construct(DOMDocument $dom) {
    parent::__construct($dom);

    $this->registerNamespace("fhir", self::FHIR_NAMESPACE);
  }

    /**
     * @param              $query
     * @param DOMNode|null $contextNode
     *
     * @return string|null
     * @throws Exception
     */
  public function getAttributeValue($query, DOMNode $contextNode = null) {
    return $this->queryAttributNode($query, $contextNode, "value");
  }
}
