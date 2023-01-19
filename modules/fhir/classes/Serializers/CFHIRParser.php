<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Serializers;

use DOMElement;
use DOMNode;
use DOMNodeList;
use Ox\Core\CMbXMLDocument;
use Ox\Interop\Fhir\Api\Request\CRequestFormats;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Psr\SimpleCache\InvalidArgumentException;

class CFHIRParser
{
    /** @var mixed */
    private $data;

    /** @var string */
    private $format;

    /** @var CFHIRResource */
    private $resource;

    /** @var CFHIRXPath */
    private $xpath;

    /** @var CMbXMLDocument */
    private $dom;

    /**
     * CFHIRParser constructor.
     *
     * @param string      $data
     * @param string|null $format
     */
    public function __construct(string $data, ?string $format)
    {
        $this->data   = $data;
        $this->format = $format ? $this->setFormat($format) : $this->determineFormat();
    }

    /**
     * @param string $format
     *
     * @return string
     * @throws CFHIRException
     */
    private function setFormat(string $format): string
    {
        if (!$format = CRequestFormats::getFormatSupported($format)) {
            throw new CFHIRException("The format '$format' for Parser is not supported");
        }

        return $this->format = $format;
    }

    /**
     * @return string
     * @throws CFHIRException
     */
    private function determineFormat(): string
    {
        if (is_string($this->data)) {
            // is json
            if (strpos($this->data, '{') === 0) {
                return CRequestFormats::CONTENT_TYPE_JSON;
            }

            // is xml
            if (strpos($this->data, '<?xml') === 0 || strpos($this->data, '<') === 0) {
                return CRequestFormats::CONTENT_TYPE_XML;
            }
        }

        throw new CFHIRException("Impossible to detect format for parsing data");
    }

    /**
     * @return bool
     */
    private function isJsonFormat(): bool
    {
        return $this->format === CRequestFormats::CONTENT_TYPE_JSON;
    }

    private function initialize(): void
    {
        $data = $this->data;

        // is json
        if ($this->isJsonFormat()) {
            $dom = $this->transformJsonInXml($data);
        } else {
            $dom = new CMbXMLDocument();
            $dom->loadXML($data);
        }

        $this->xpath = new CFHIRXPath($dom);
        $this->dom   = $dom;
    }

    /**
     * @param string|array $data
     * @param string|null $format
     *
     * @return CFHIRResource|null
     */
    public static function tryToDetermineResource($data, ?string $format = null): ?CFHIRResource
    {
        $serializer = new self($data, $format);

        // initialize parsing
        $serializer->initialize();

        if (!$serializer->dom->documentElement) {
            return null;
        }

        // determine resource
        return $serializer->determineResource($serializer->dom->documentElement);
    }

    public function determineResource(DOMNode $node): ?CFHIRResource
    {
        $resource_type = $node->nodeName;
        $profiles      = $this->xpath->query('fhir:meta/fhir:profile');

        $canonical = CFHIR::BASE_PROFILE . $resource_type;
        if (count($profiles) === 1) {
            $node_profile       = $profiles->item(0);
            $canonical_profiled = $node_profile->attributes->getNamedItem('value')->textContent;
        }

        $map = new FHIRClassMap();
        // try to retrieve class for profiled resource
        if (isset($canonical_profiled) && $canonical_profiled) {
            try {
                return $map->resource->getResource($canonical_profiled);
            } catch (CFHIRException $exception) {
            }
        }

        // try to retrieve class for international resource
        try {
            return $resource ?? $map->resource->getResource($canonical);
        } catch (CFHIRException $exception) {
            // resource not supported
            return null;
        }
    }

    /**
     * @param string $data
     *
     * @return string
     */
    private function transformJsonInXml(string $json): CMbXMLDocument
    {
        $data = json_decode($json, false);

        $this->dom = new CMbXMLDocument('UTF-8');
        if ($resource = $this->handleJsonResource($data)) {
            $this->dom->appendChild($resource);
        }

        return $this->dom;
    }

    /**
     * @param object|null $object
     *
     * @return DOMNode|null
     */
    private function handleJsonResource(?object $object): ?DOMNode
    {
        if (!$object || !$resource_type = $object->resourceType) {
            return null;
        }

        unset($object->resourceType);

        return $this->handleJsonObject($resource_type, $object);
    }

    /**
     * @param string             $field
     * @param mixed|array|object $value
     *
     * @return array|DOMElement|DOMNode|null
     */
    private function handleJsonElements(string $field, $value)
    {
        if (is_object($value)) {
            return $this->handleJsonObject($field, $value);
        }

        if (is_array($value)) {
            return $this->handleJsonArray($field, $value);
        }

        return $this->handleJsonPrimitive($field, $value);
    }

    /**
     * @param string $field
     * @param array  $value
     *
     * @return array
     */
    private function handleJsonArray(string $field, array $value): array
    {
        $resources = [];
        foreach ($value as $value_child) {
            $element     = $this->handleJsonElements($field, $value_child);
            $resources[] = $element;
        }

        return $resources;
    }

    /**
     * @param string $field
     * @param object $object
     *
     * @return DOMNode|null
     */
    private function handleJsonObject(string $field, object $object): ?DOMNode
    {
        $resource = $this->dom->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $field);
        if (isset($object->resourceType)) {
            $element = $this->handleJsonResource($object);
            $resource->appendChild($element);

            return $resource;
        }

        foreach ($object as $object_field => $value) {
            if (strpos($object_field, '_') === 0) {
                continue;
            }

            if ($field === 'extension' && $object_field === 'url') {
                $resource->setAttribute('url', $value);

                continue;
            } else {
                $elements = $this->handleJsonElements($object_field, $value);
            }

            if (!is_array($elements)) {
                if (isset($object->{"_$object_field"})) {
                    // retrieve data
                    $extension_data = $object->{"_$object_field"};

                    // clean object
                    unset($object->{"_$object_field"});

                    // handle id on primitive datatype
                    if (isset($extension_data->id) && $extension_data->id) {
                        $elements->setAttribute('id', $extension_data->id);
                    }

                    // handle extension on primitive datatype
                    if (isset($extension_data->extension) && count($extension_data->extension) > 0) {
                        $extension_elements = $this->handleJsonElements('extension', $extension_data->extension);
                        if (!is_array($extension_elements)) {
                            $extension_elements = [$extension_elements];
                        }

                        // append extension element
                        foreach ($extension_elements as $extension) {
                            $elements->appendChild($extension);
                        }
                    }
                }

                $elements = [$elements];
            }

            $elements = array_filter($elements);
            foreach ($elements as $element) {
                $resource->appendChild($element);
            }
        }

        return $resource;
    }

    /**
     * @param string          $field
     * @param string|bool|int $value
     *
     * @return DOMElement
     */
    private function handleJsonPrimitive(string $field, $value): DOMElement
    {
        // sanitize value
        if ($value === true) {
            $value = 'true';
        } elseif ($value === false) {
            $value = 'false';
        }

        $element = $this->dom->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $field);
        $element->setAttribute('value', $value);

        return $element;
    }

    /**
     * @param string|array $data
     * @param string|null  $format
     *
     * @return self
     * @throws InvalidArgumentException
     */
    public static function parse($data, ?string $format = null): ?self
    {
        $serializer = new self($data, $format);

        // initialize parsing
        $serializer->initialize();

        if (!$serializer->dom->documentElement) {
            return $serializer;
        }

        // determine resource
        $serializer->resource = $serializer->determineResource($serializer->dom->documentElement);

        if (!$serializer->resource) {
            return $serializer;
        }

        $node     = $serializer->dom->documentElement;
        $resource = $serializer->getResource();
        $serializer->handle($node, $resource);

        return $serializer;
    }

    /**
     * @param DOMNode       $node
     * @param CFHIRResource $resource
     *
     * @throws InvalidArgumentException
     */
    public function handle(DOMNode $node, CFHIRResource $resource): void
    {
        $fields = CFHIRDefinition::getFields($resource);
        foreach ($fields as $field) {
            $definition = CFHIRDefinition::getElementDefinition($resource, $field);

            // map only elements defined
            if (!property_exists($resource, $field)) {
                continue;
            }

            // retrieve datatype
            $datatype_class = $definition['datatype']['class'];
            $is_array       = $definition['datatype']['is_array'];

            // search element nodes for field
            $search_fields = [$field => $datatype_class];
            // Dans le cas d'un choix entre plusieurs type, on cherche le bon noeud
            if ($datatype_class === CFHIRDataTypeChoice::class) {
                $search_fields = [];
                foreach ($definition['datatype']['sub_types'] as $sub_datatype) {
                    $search_fields[$field . '' . ucfirst($sub_datatype::NAME)] = $sub_datatype;
                }
            }

            // search nodes
            $element_nodes = new DOMNodeList();
            foreach ($search_fields as $search_field => $_datatype_class) {
                // Si on a trouvé un noeud qui correspond on s'arrete de chercher
                if ($element_nodes->count() > 0) {
                    continue;
                }

                // search node
                $element_nodes  = $this->xpath->query("fhir:$search_field", $node);
                $datatype_class = $_datatype_class;
            }

            $elements = [];
            foreach ($element_nodes as $element) {
                /** @var CFHIRDataType $datatype */
                $datatype = new $datatype_class();

                // mapping
                $datatype->fromXML($element, $this->xpath, $this);

                // keep only if not null
                if (!$datatype->isNull()) {
                    $elements[] = $datatype;
                }
            }

            if ($elements) {
                // map resource on datatype
                foreach ($elements as $element) {
                    $element->setParent($resource);
                    $element->setParentResource($resource);
                }

                $first_element = reset($elements);

                $method = 'set' . ucfirst($field);
                if (method_exists($resource, $method)) {
                    if ($is_array) {
                        $resource->$method(...$elements);
                    } else {
                        $resource->$method($first_element ?: null);
                    }
                } else {
                    $resource->{$field} = $is_array ? $elements : ($first_element ?: null);
                }
            }
        }
    }

    public function getResource(): ?CFHIRResource
    {
        return $this->resource;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return CMbXMLDocument
     */
    public function getDom(): CMbXMLDocument
    {
        return $this->dom;
    }

    /**
     * @return CFHIRXPath
     */
    public function getXpath(): CFHIRXPath
    {
        return $this->xpath;
    }
}
