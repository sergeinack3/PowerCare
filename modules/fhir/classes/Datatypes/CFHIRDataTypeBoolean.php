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
class CFHIRDataTypeBoolean extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Boolean';

    /**
     * @param string|bool|int $value
     */
    public function setValue($value): self
    {
        if (is_string($value)) {
            $value = $value === 'false' ? false : true;
        }

        if (is_int($value)) {
            $value = $value === 0 ? false : true;
        }

        parent::setValue($value);

        return $this;
    }

    /**
     * @return bool
     */
    public function getValue()
    {
        return (bool) $this->_value;
    }

    public function toXML(DOMElement $node, string $field, DOMDocument $document): void
    {
        $element = $document->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $field);
        $value = $this->getValue() ? "true" : "false";
        $element->setAttribute('value', $value);

        // id && extension
        $this->extensionToXml($element, $document);

        $node->appendChild($element);
    }
}
