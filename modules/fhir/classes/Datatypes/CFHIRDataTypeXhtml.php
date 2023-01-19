<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes;

use DOMDocument;
use DOMElement;
use Ox\Interop\Fhir\CFHIRXPath;

/**
 * FHIR data type
 */
class CFHIRDataTypeXhtml extends CFHIRDataTypeString
{
    /** @var string */
    public const NAME = 'Xhtml';

    /**
     * @inheritdoc
     */
    public function toXML(DOMElement $node, string $field, DOMDocument $document): void
    {
        $element = $document->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $field);

        // id && extension
        $this->extensionToXml($element, $document);

        // set content
        $element->textContent = $this->getValue();

        $node->appendChild($element);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return "<div xmlns='http://www.w3.org/1999/xhtml'>" . utf8_encode($this->_value) . "</div>";
    }
}
