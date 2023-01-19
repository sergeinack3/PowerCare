<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Serializers;

use Ox\Core\CMbXMLDocument;
use Ox\Interop\Fhir\Api\Request\CRequestFormats;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Utilities\CFHIRTools;
use Psr\SimpleCache\InvalidArgumentException;

class CFHIRSerializer
{
    /** @var string */
    public const OPTION_OUTPUT_PRETTY = 'pretty';

    /** @var CFHIRResource */
    private $resource;

    /** @var string */
    private $format;

    /** @var array */
    private $options;

    /** @var string */
    private $resource_serialized;

    /** @var CMbXMLDocument */
    private $dom;

    /** @var array|null */
    private $json_data;

    public function __construct(CFHIRResource $resource, string $format, array $options = [])
    {
        $this->setFormat($format);
        $this->setOptions($options);
        $this->resource = $resource;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        if (!$format = CRequestFormats::getFormatSupported($format)) {
            throw new CFHIRException("The format '$format' for Serializer is not supported");
        }

        $this->format = $format;
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options): void
    {
        $default_options = $this->getDefaultOptions();

        foreach ($options as $key => $option) {
            if (!array_key_exists($key, $default_options)) {
                continue;
            }

            $default_options[$key] = $option;
        }

        $this->options = $default_options;
    }

    protected function getDefaultOptions(): array
    {
        return [
            self::OPTION_OUTPUT_PRETTY => false,
        ];
    }

    /**
     * @return bool
     */
    private function isXml(): bool
    {
        return $this->format === CRequestFormats::CONTENT_TYPE_XML;
    }

    /**
     * @param CFHIRResource $resource
     * @param string        $format
     * @param array         $options
     *
     * @return static
     * @throws InvalidArgumentException
     */
    public static function serialize(
        CFHIRResource $resource,
        string $format = CRequestFormats::CONTENT_TYPE_XML,
        array $options = []
    ): self {
        $serializer = new self($resource, $format, $options);

        if ($serializer->isXml()) {
            $content = $serializer->serializeXML();
        } else {
            $content = $serializer->serializeJSON();
        }

        $serializer->resource_serialized = $content;

        return $serializer;
    }

    /**
     * @return string
     */
    public function getResourceSerialized(): string
    {
        return $this->resource_serialized;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public function serializeXML(): string
    {
        $this->dom               = new CMbXMLDocument("UTF-8");
        $this->dom->formatOutput = $this->options[self::OPTION_OUTPUT_PRETTY];

        $resource = $this->dom->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $this->resource::RESOURCE_TYPE);
        foreach (CFHIRTools::getNonEmptyFields($this->resource) as $field => $datatypes) {
            if (!is_array($datatypes)) {
                $datatypes = [$datatypes];
            }

            foreach ($datatypes as $datatype) {
                $datatype->toXML($resource, $field, $this->dom);
            }
        }

        $this->dom->appendChild($resource);

        return $this->dom->saveXML();
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    private function serializeJSON(): string
    {
        $data         = CFHIRTools::getNonEmptyFields($this->resource);
        $flags        = $this->options[self::OPTION_OUTPUT_PRETTY] ? JSON_PRETTY_PRINT : 0;
        $data_to_json = (new CFHIRDataTypeString($this->resource::RESOURCE_TYPE))->toJSON('resourceType');

        foreach ($data as $field => $datatypes) {
            if (is_array($datatypes)) {
                $values = CFHIRTools::manageDatatypeJSONArray($datatypes, $field);
            } else {
                /** @var CFHIRDataType $datatypes */
                $values = $datatypes->toJSON($field);
            }

            $data_to_json = array_merge($data_to_json, $values);
        }

        $this->json_data = $data_to_json;

        return json_encode($data_to_json, $flags + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return CMbXMLDocument|null
     */
    public function getDom(): ?CMbXMLDocument
    {
        return $this->dom;
    }

    /**
     * @return array|null
     */
    public function getJsonData(): ?array
    {
        return $this->json_data;
    }
}
