<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use DOMDocument;
use DOMElement;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Ox\Interop\Fhir\Serializers\CFHIRSerializer;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * FHIR data type
 */
class CFHIRDataTypeResource extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Resource';

    /** @var bool */
    private $resource_not_supported = false;

    /** @var string */
    private $resource_type;

    /**
     * @param DOMElement  $node
     * @param string      $field
     * @param DOMDocument $document
     */
    public function toXML(DOMElement $node, string $field, DOMDocument $document): void
    {
        /** @var CFHIRResource $resource */
        if (!$resource = $this->getValue()) {
            return;
        }

        $serializer = CFHIRSerializer::serialize($resource, 'xml');
        if (!$dom =$serializer->getDom()) {
            return;
        }

        if (!$element = $dom->documentElement) {
            return;
        }
        $node_resource = $document->importNode($element, true);

        $element_resource = $document->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $field);
        $element_resource->appendChild($node_resource);

        $node->appendChild($element_resource);
    }

    /**
     * @param string $field
     *
     * @return array|null
     */
    public function toJSON(string $field): ?array
    {
        if ($this->isNull()) {
            return null;
        }

        if (!$this->getValue()) {
            return [];
        }

        $serializer = CFHIRSerializer::serialize($this->getValue(), 'json');

        return [$field => $serializer->getJsonData()];
    }

    /**
     * @return CFHIRResource|null
     */
    public function getValue()
    {
        return parent::getValue();
    }

    /**
     * @return array
     * @throws InvalidArgumentException
     */
    public function getDefinition(): array
    {
        return $this->_value ? CFHIRDefinition::getDefinition($this->_value) : [];
    }

    /**
     * @param DOMElement  $element
     * @param CFHIRXPath  $xpath
     * @param CFHIRParser $parser
     *
     * @throws InvalidArgumentException
     */
    public function fromXML(DOMElement $element, CFHIRXPath $xpath, CFHIRParser $parser): void
    {
        $node                = $element->firstChild;
        $this->resource_type = $node->nodeName;
        if (!$resource = $parser->determineResource($node)) {
            $this->resource_not_supported = true;

            return;
        }

        $this->_value = $resource;

        $parser->handle($node, $resource);
    }

    /**
     * @return bool
     */
    public function isResourceNotSupported(): bool
    {
        return $this->resource_not_supported;
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return $this->_value === null && !$this->extension && $this->resource_not_supported === false;
    }
}
