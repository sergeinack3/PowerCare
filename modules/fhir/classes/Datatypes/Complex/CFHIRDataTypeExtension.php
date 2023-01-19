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
use Exception;
use Ox\Core\CMbArray;
use Ox\Interop\Fhir\CFHIRXPath;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDecimal;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInteger;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Exception\CFHIRException;
use Ox\Interop\Fhir\Serializers\CFHIRParser;

/**
 * FHIR data type
 */
class CFHIRDataTypeExtension extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Extension';

    /** @var CFHIRDataTypeUri */
    public $url;

    /** @var CFHIRDataType */
    public $value;

    public static function build(array $data)
    {
        $url       = CMbArray::extract($data, 'url');
        $id        = CMbArray::extract($data, 'id');
        $extension = CMbArray::extract($data, 'extension');
        $datatype  = parent::build(['url' => $url, 'extension' => $extension, 'id' => $id]);

        // try to find value
        if ($value = CMbArray::extract($data, 'value')) {
            if ($value instanceof CFHIRDataType) {
                $datatype->value = $value;
            } elseif (is_int($value)) {
                $datatype->value = new CFHIRDataTypeInteger($value);
            } elseif (is_float($value)) {
                $datatype->value = new CFHIRDataTypeDecimal($value);
            } elseif (is_string($value)) {
                $datatype->value = new CFHIRDataTypeString($value);
            }

            return $datatype;
        }

        // try to find value[x]
        foreach ($data as $field => $value) {
            if (preg_match("/^value{1}(?'type'[A-Z]{1}\w+)$/", $field, $matches)) {
                $type = CMbArray::get($matches, 'type', CFHIRDataTypeString::NAME);
                if ($value instanceof CFHIRDataType && $type == $value::NAME) {
                    $_datatype = $value;
                } else {
                    try {
                        $founded_datatype = self::get($type, false);
                    } catch (Exception $exception) {
                        $founded_datatype = CFHIRDataTypeString::class;
                    }

                    $_datatype = is_subclass_of(CFHIRDataTypeComplex::class, $founded_datatype)
                        ? $founded_datatype::build($value)
                        : new $founded_datatype($value);
                }

                // instance of datatype founded
                $datatype->value = $_datatype;

                return $datatype;
            }
        }

        return $datatype;
    }

    /**
     * @param string $url
     * @param array  $data
     *
     * @return static
     */
    public static function addExtension(string $url, array $data): self
    {
        return self::build(
            array_merge(["url" => $url], $data)
        );
    }

    public function toXML(DOMElement $node, string $field, DOMDocument $document): void
    {
        $element = $document->createElementNS(CFHIRXPath::FHIR_NAMESPACE, $field);

        if ($this->isNull()) {
            return;
        }

        // id
        if ($this->id && !$this->id->isNull()) {
            $element->setAttribute('id', $this->id->getValue());
        }

        // url
        if (!$this->url->isNull()) {
            $element->setAttribute('url', $this->url->getValue());
        }

        // value
        if ($this->value && !$this->value->isNull()) {
            $type = ucfirst($this->value::NAME);
            $this->value->toXML($element, "value$type", $document);
        } else {
            // extension
            $this->extensionToXml($element, $document);
        }

        $node->appendChild($element);
    }

    /**
     * @return bool
     */
    public function isNull(): bool
    {
        return !$this->url || $this->url->isNull() ||  CFHIRDataType::isNull();
    }

    /**
     * @return mixed|CFHIRDataType
     */
    public function getValue()
    {
        return $this->value;
    }

    public function toJSON(string $field): ?array
    {
        $values = [];

        if ($this->isNull()) {
            return null;
        }

        // url
        $values[$field]['url'] = $this->url->getValue();

        // extension
        if ($this->extension && is_array($this->extension)) {
            return $this->extensionToJson($values, $field);
        }

        // value[x]
        if ($this->value && !$this->value->isNull()) {
            $type           = ucfirst($this->value::NAME);
            $values[$field] = array_merge($values[$field], $this->value->toJSON("value$type"));
        }

        return $values;
    }

    public function fromXML(DOMElement $element, CFHIRXPath $xpath, CFHIRParser $parser): void
    {
        if ($url = $element->attributes->getNamedItem('url')) {
            $this->url = new CFHIRDataTypeUri($url->textContent);
            $this->url->_parent = $this;
            $this->url->_parent_resource = &$this->_parent_resource;
        }

        if (!$node = $element->firstChild) {
            return;
        }

        $node_name = $node->nodeName;

        // treat Value[x] element
        if (str_starts_with($node_name, 'value')) {
            $type = substr($node_name, strlen('value'));
            try {
                $datatype = CFHIRDataType::get($type);
                $datatype->_parent = $this;
                $datatype->_parent_resource = &$this->_parent_resource;
                $datatype->fromXML($node, $xpath, $parser);

                $this->value = $datatype;
            } catch (CFHIRException $exception) {
            }
        }

        // treat Extension element
        if ($node_name === 'extension') {
            $extension = new CFHIRDataTypeExtension();
            $extension->_parent = $this;
            $extension->_parent_resource = &$this->_parent_resource;
            $extension->fromXML($node, $xpath, $parser);
            $this->extension[] = $extension;
        }
    }
}
