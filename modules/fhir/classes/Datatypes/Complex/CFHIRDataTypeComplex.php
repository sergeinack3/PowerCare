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
use DOMException;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Resources\R4\CFHIRDefinition;
use Ox\Interop\Fhir\Serializers\CFHIRParser;
use Ox\Interop\Fhir\Utilities\CFHIRTools;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * FHIR data type
 */
class CFHIRDataTypeComplex extends CFHIRDataType
{
    /** @var string */
    public const NAME = 'Complex';

    /**
     * Builds a component from data
     *
     * @param array $data data
     *
     * @return static
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public static function build(array $data)
    {
        $object = new static();
        foreach ($data as $_key => $_value) {
            if ($_value === null) {
                continue;
            }

            // if datatype need to be an array and only one element is given
            if ($object->isDataTypeElementIsArray($_key) && !is_array($_value)) {
                $_value = [$_key => $_value];
            }

            // todo si on donne un tableau et qu'il ne faut qu'un seul élément ?
            // todo prendre le premier ? que s'il y a qu'un élément ? (l'inverse de ce qui a au dessus)

            $value = [];
            // build multiple elements
            if (is_array($_value)) {
                CMbArray::removeValue(null, $_value, true);
                CMbArray::removeValue("", $_value, true);

                foreach ($_value as $_val) {
                    $value[] = $object->buildValue($_key, $_val);
                }
            } else {
                // build only one element
                $value = $object->buildValue($_key, $_value);
            }

            // skip if no data
            if ($value === null || empty($value)) {
                continue;
            }

            // map parent and resource
            $fields = $value;
            if (!is_array($fields)) {
                $fields = [$fields];
            }
            foreach ($fields as $_field) {
                $_field->_parent = $object;
                $_field->_resource =& $object->_parent_resource;
            }

            // apply datatype
            $object->$_key = $value;
        }

        return $object;
    }

    /**
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isNull(): bool
    {
        $fields = $this->isSummary() ? CFHIRDefinition::getSummariesFields($this)
            : CFHIRDefinition::getFields($this);

        $is_null = true;
        foreach ($fields as $key) {
            /** @var CFHIRDataType $v */
            $v = $this->{$key};
            if ($v === null || (!$v instanceof CFHIRDataType && !is_array($v))) {
                continue;
            }

            if (is_array($v) && count(array_filter($v)) > 0) {
                return false;
            }

            if ($v instanceof CFHIRDataType && !$v->isNull()) {
                return false;
            }
        }

        return $is_null && !$this->extension;
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return CFHIRDataType
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function buildValue(string $key, $value): ?CFHIRDataType
    {
        /** @var CFHIRDataType $target_datatype_class */
        $target_datatype_class = $this->getDataTypeElement($key);

        // case of primitive datatype
        if (is_object($value) && $value instanceof CFHIRDataType) {
            return $value;
        }

        // treat complex type
        if ($target_datatype_class instanceof CFHIRDataTypeComplex || $value instanceof CFHIRDataTypeComplex) {
            /** @var CFHIRDataType $target_datatype */
            $target_datatype = new $target_datatype_class();

            return $target_datatype->build($value);
        }

        // treat primitive type
        return new $target_datatype_class($value);
    }

    /**
     * @param string $field
     *
     * @return array[]|null
     * @throws InvalidArgumentException
     */
    public function toJSON(string $field): ?array
    {
        $data = [];
        foreach (CFHIRTools::getNonEmptyFields($this) as $object_field => $datatypes) {
            if (is_array($datatypes)) {
                $values = CFHIRTools::manageDatatypeJSONArray($datatypes, $object_field);
            } else {
                /** @var CFHIRDataType $datatype */
                $datatype = $datatypes;
                $values   = $datatype->toJSON($object_field);
            }

            $data = array_merge($data ?? [], $values);
        }

        return [$field => $data];
    }

    /**
     * @param DOMElement  $node
     * @param string      $field
     * @param DOMDocument $document
     *
     * @return void
     * @throws InvalidArgumentException
     * @throws DOMException
     */
    public function toXML(DOMElement $node, string $field, DOMDocument $document): void
    {
        $element = $document->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $field);


        // id && extension
        $this->extensionToXml($element, $document);

        // fields
        foreach (CFHIRTools::getNonEmptyFields($this) as $_field => $values) {
            if ($_field == 'extension') {
                continue;
            }
            if (!is_array($values)) {
                $values = [$values];
            }

            /** @var CFHIRDataType $datatype_value */
            foreach ($values as $datatype_value) {
                $datatype_value->toXML($element, $_field, $document);
            }
        }

        $node->appendChild($element);
    }

    /**
     * @param DOMElement $element
     * @param CFHIRXPath $xpath
     *
     * @throws InvalidArgumentException
     */
    public function fromXML(DOMElement $element, CFHIRXPath $xpath, CFHIRParser $parser): void
    {
        foreach ($this->getDefinition() as $definition) {
            $field = $definition['field'];
            $nodes = $xpath->query("fhir:$field", $element);
            if ($nodes->count() === 0) {
                continue;
            }

            $datatype_class = $definition['datatype']['class'];
            $is_array       = $definition['datatype']['is_array'];

            $datatype_elements = [];
            foreach ($nodes as $node) {
                /** @var CFHIRDataType $datatype */
                $datatype = new $datatype_class();
                $datatype->_parent = $this;
                $datatype->_parent_resource = &$this->_parent_resource;
                $datatype->fromXML($node, $xpath, $parser);

                $datatype_elements[] = $datatype;
            }

            $datatype_element = reset($datatype_elements);
            $this->{$field}   = $is_array
                ? $datatype_elements
                : ($datatype_element ?: null);
        }
    }
}
