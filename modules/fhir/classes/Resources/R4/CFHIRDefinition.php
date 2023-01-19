<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use Exception;
use Ox\Components\Cache\LayeredCache;
use Ox\Core\CClassMap;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Psr\SimpleCache\InvalidArgumentException;

class CFHIRDefinition
{
    /** @var string */
    private const RESOURCE_DEFINITION_FILE_NAME = 'profiles-resources-definition.xml';

    /** @var string */
    private const DATATYPE_DEFINITION_FILE_NAME = 'profiles-types-definition.xml';

    /** @var string */
    private const DEFINITION_DIR_PATH = 'interopResources/resources/fhir';

    /** @var string */
    private const CACHE_DEFINITION_NAME = 'fhir_definition';

    /** @var self */
    private static $instance;

    /** @var string */
    private $definition_dir_path;

    /** @var CFHIRXPath */
    private $xpath;

    /** @var array */
    public $definition = [];

    /** @var LayeredCache */
    private $cache;

    /**
     * CFHIRDefinition constructor.
     */
    protected function __construct(LayeredCache $cache = null)
    {
        $this->definition_dir_path = dirname(__DIR__, 4) . '/' . self::DEFINITION_DIR_PATH;
        $this->definition          = [];
        $this->cache               = $cache ?: $this->getCache();
    }

    /**
     * @return LayeredCache
     * @throws \Ox\Components\Cache\Exceptions\CouldNotGetCache
     * @throws InvalidArgumentException
     */
    private function getCache(): LayeredCache
    {
        $cache = LayeredCache::getCache(LayeredCache::INNER_OUTER);

        if ($data = $cache->get(self::CACHE_DEFINITION_NAME)) {
            $this->definition = $data;
        }

        return $cache;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function clearCache(): bool
    {
        $instance             = self::get();
        $instance->definition = [];

        return $instance->cache->delete(self::CACHE_DEFINITION_NAME);
    }

    /**
     * @throws Exception
     */
    private function searchElement(string $canonical): ?DOMElement
    {
        return $this->xpath->queryUniqueNode(
            "/fhir:Bundle/fhir:entry[fhir:fullUrl/@value='$canonical']/fhir:resource/fhir:StructureDefinition"
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    private function buildDefinition(): void
    {
        // resource
        $this->buildResourcedDefinition();

        // datatype
        $this->buildDatatypeDefinition();

        // Store cache
        $this->cache->set(
            self::CACHE_DEFINITION_NAME,
            $this->definition
        );
    }

    /**
     * @throws Exception
     */
    private function buildResourcedDefinition(): void
    {
        // Ox fhir resources
        $map       = new FHIRClassMap();
        $resources = $map->resource->listResources(null, CFHIR::class);

        // load file
        $path = $this->definition_dir_path . '/' . self::RESOURCE_DEFINITION_FILE_NAME;
        $dom  = new DOMDocument();
        $dom->load($path);
        $this->xpath = new CFHIRXPath($dom);

        // build elements
        foreach ($resources as $resource) {
            // Find structure definition for type
            $profile              = $resource->getProfile();
            $structure_definition = $this->searchElement($profile);

            // select all fhir resources elements
            $elements = $this->xpath->query('fhir:snapshot/fhir:element', $structure_definition);

            $this->buildDefinitionElements($elements, $resource::RESOURCE_TYPE);
        }
    }

    /**
     * @throws Exception
     */
    private function buildDatatypeDefinition(): void
    {
        // Load datatype classes
        $class_map         = CClassMap::getInstance();
        $datatype_elements = $class_map->getClassChildren(CFHIRDataType::class, false, true);

        // load file
        $path = $this->definition_dir_path . '/' . self::DATATYPE_DEFINITION_FILE_NAME;
        $dom  = new DOMDocument();
        $dom->load($path);
        $this->xpath = new CFHIRXPath($dom);

        // build elements
        /** @var CFHIRDataType $datatype */
        foreach ($datatype_elements as $datatype) {
            // Find structure definition for type
            $profile              = CFHIR::BASE_PROFILE . $datatype::NAME;
            $structure_definition = $this->searchElement($profile);

            // select all elements
            $elements = $this->xpath->query('fhir:snapshot/fhir:element', $structure_definition);

            $this->buildDefinitionElements($elements, $datatype::NAME);
        }
    }

    /**
     * @param DOMNodeList $elements
     * @param string      $name
     *
     * @throws Exception
     */
    private function buildDefinitionElements(DOMNodeList $elements, string $name): void
    {
        $definition = [];

        /** @var DOMNode $element */
        foreach ($elements as $element) {
            if (!$element->localName) {
                continue;
            }

            // build each fhir resource element
            if ($definition_element = $this->buildDefinitionElement($element)) {
                $definition['elements'][] = $definition_element;
            }
        }

        $this->definition[$name] = $definition;
    }

    /**
     * @param DOMNode $element
     *
     * @return array
     * @throws Exception
     */
    private function buildDefinitionElement(DOMNode $element): array
    {
        $element_id = $element->attributes->getNamedItem('id')->textContent;
        $explode    = explode('.', $element_id);
        if (count($explode) < 2) {
            return [];
        }


        $min        = $this->xpath->getAttributeValue('fhir:base/fhir:min', $element);
        $max        = $this->xpath->getAttributeValue('fhir:base/fhir:max', $element);
        $is_summary = $this->xpath->getAttributeValue('fhir:isSummary', $element) === "true";

        $extension_profile = 'http://hl7.org/fhir/StructureDefinition/structuredefinition-fhir-type';
        $type_node         = $this->xpath->query('fhir:type', $element);
        if ($extension_type = $this->xpath->queryUniqueNode(
            "fhir:type[1]/fhir:extension[@url='$extension_profile']",
            $element
        )) {
            $type = $this->xpath->getAttributeValue('fhir:valueUrl', $extension_type);
        } else {
            $type = $this->xpath->getAttributeValue('fhir:code', $type_node->item(0));
        }

        $is_sub_element = false;
        try {
            // element defined without type
            if ($type === null) {
                return [];
            }

            // retrieve datatype class
            $datatype = CFHIRDataType::get($type, false);
            if ($datatype === CFHIRDataTypeBackboneElement::class) {
                $datatype       = CFHIRDataType::get($element_id, false);
                $is_sub_element = true;
            }
        } catch (Exception $exception) {
            // element not managed
            return [];
        }

        $sub_types = [];
        // case for CFHIRDatatypeChoice
        if (count($type_node) > 1) {
            $datatype = CFHIRDataTypeChoice::class;
            /** @var DOMNode $node */
            foreach ($type_node as $node) {
                try {
                    if ($sub_type = $this->xpath->getAttributeValue('fhir:code', $node)) {
                        $sub_types[] = CFHIRDataType::get($sub_type, false);
                    }
                } catch (CFHIRException $exception) {
                }
            }
        }

        $field       = end($explode);
        $is_required = intval($min) > 0;
        $is_multiple = $max === '*' ? true : intval($max) > 1;

        return [
            'element_id'     => $element_id,
            'field'          => $field,
            'is_required'    => $is_required,
            'datatype'       => [
                'class'     => $datatype,
                'is_array'  => $is_multiple,
                'sub_types' => $sub_types,
            ],
            'is_sub_element' => $is_sub_element,
            'is_summary'     => $is_summary,
        ];
    }

    /**
     * @param string|CFHIRResource|CFHIRDataType $resource
     *
     * @return array|null
     * @throws InvalidArgumentException
     */
    public static function getDefinition($resource): ?array
    {
        if (gettype($resource) === 'string') {
            $resource = self::getResourceObject($resource);
        }

        $type     = self::getType($resource);
        $instance = self::get();

        if (!$instance->definition) {
            $instance->buildDefinition();
        }

        if ($resource instanceof CFHIRDataTypeBackboneElement) {
            $resource_type = explode('.', $resource::NAME)[0];
            if (!$resource_target = (new FHIRClassMap())->resource->getResource($resource_type)) {
                throw new CFHIRException(
                    "The name of directory for the backbone element " . get_class(
                        $resource
                    ) . ' is not a valid name of fhir resource'
                );
            }

            $field = substr($resource::NAME, strlen($resource_type) + 1);

            return ['elements' => self::getSubElementsDefinition($resource_target, $field)];
        }

        return $instance->definition[$type] ?? null;
    }

    /**
     * @param string|CFHIRDataType|CFHIRResource $resource
     */
    private static function getResourceObject($resource)
    {
        if (is_object($resource)) {
            if ($resource instanceof CFHIRResource || $resource instanceof CFHIRDataType) {
                return $resource;
            }
        }

        $is_resource = is_subclass_of($resource, CFHIRResource::class);
        $is_datatype = $is_resource ? false : is_subclass_of($resource, CFHIRDataType::class);
        if ($is_resource || $is_datatype) {
            return new $resource();
        }

        throw new CFHIRException(
            "'$resource' is not a valid resource, it should be extends from " . CFHIRResource::class . ' Or ' . CFHIRDataType::class
        );
    }

    /**
     * @param $resource
     *
     * @return string|null
     */
    private static function getType($resource): string
    {
        if (!is_object($resource)) {
            $resource = self::getResourceObject($resource);
        }

        if ($resource instanceof CFHIRResource) {
            return $resource::RESOURCE_TYPE;
        }

        if ($resource instanceof CFHIRDataType) {
            return $resource::NAME;
        }

        throw new CFHIRException(
            "'$resource' is not a valid resource, it should be extends from " . CFHIRResource::class . ' Or ' . CFHIRDataType::class
        );
    }

    /**
     * @return CFHIRDefinition
     */
    public static function get(): CFHIRDefinition
    {
        if (!$instance = self::$instance) {
            $instance = new self();
        }

        return self::$instance = $instance;
    }

    /**
     * @param string|CFHIRResource|CFHIRDataType $type
     *
     * @return array|null
     * @throws InvalidArgumentException
     */
    public static function getFields($resource): ?array
    {
        $definition = self::getDefinition($resource);

        if (!$definition || !$definition['elements']) {
            return null;
        }

        $type = self::getType($resource);

        // only main elements
        $regex = self::makeRegSearchElementId($type);

        $definition_elements = array_filter(
            $definition['elements'],
            function ($data) use ($regex) {
                return preg_match("/$regex/", $data['element_id']);
            }
        );

        return self::mapFields($definition_elements);
    }


    /**
     * @param string|CFHIRResource|CFHIRDataType $resource
     * @param string                             $field
     *
     * @return array|null
     * @throws InvalidArgumentException
     */
    public static function getElementDefinition($resource, string $field): ?array
    {
        $definition = self::getDefinition($resource);
        if (!$definition || !$definition['elements']) {
            return null;
        }

        $prefix_field = (is_subclass_of($resource, CFHIRResource::class) ? $resource::RESOURCE_TYPE : $resource::NAME);
        $field        = "$prefix_field.$field";
        foreach ($definition['elements'] as $element) {
            if (rtrim($element['element_id'], "[x]") === $field) {
                return $element;
            }
        }

        return null;
    }

    /**
     * @param CFHIRResource|CFHIRDataType|string $resource
     * @param string                             $field
     *
     * @return array|null
     * @throws InvalidArgumentException
     */
    public static function getSubElementsDefinition($resource, string $fields): ?array
    {
        $definition = self::getDefinition($resource);
        if (!$definition || !$definition['elements']) {
            return null;
        }

        $type  = self::getType($resource);
        $regex = self::makeRegSearchElementId($type, $fields);

        $elements = array_filter(
            $definition['elements'],
            function ($data) use ($regex) {
                return preg_match("/$regex/", $data['element_id']);
            }
        );

        return array_values($elements);
    }

    /**
     * @param string|CFHIRResource|CFHIRDataType $type
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public static function getSummariesFields($resource): ?array
    {
        $definition = self::getDefinition($resource);
        if (!$definition || !$definition['elements']) {
            return null;
        }

        $type  = self::getType($resource);
        $regex = self::makeRegSearchElementId($type);

        // only main elements and flat like summary
        $definition_elements = array_filter(
            $definition['elements'],
            function ($data) use ($regex, $type) {
                // keep extension for summary object
                if ($data['element_id'] == "$type.extension") {
                    return true;
                }

                return preg_match("/$regex/", $data['element_id']) && $data['is_summary'];
            }
        );

        return self::mapFields($definition_elements);
    }

    /**
     * @param string      $type
     * @param string|null $fields
     *
     * @return string
     */
    private static function makeRegSearchElementId(string $type, ?string $fields = null): string
    {
        $reg = "^$type\.";
        if ($fields) {
            $fields = str_replace('.', '\.', $fields);
            $reg    .= rtrim("$fields", '\.') . '\.';
        }

        // example : $Bundle.entry.request[x]^
        return "$reg\w+(?>\[x\])?$";
    }

    private static function mapFields(array $elements): array
    {
        return array_map(
            function ($data) {
                // sanitize field (field CFHIRDatatypeChoice)
                return str_replace('[x]', '', $data['field']);
            },
            array_values($elements)
        );
    }
}
