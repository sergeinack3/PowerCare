<?php
/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Core\CMbArray;
use Ox\Interop\Fhir\ClassMap\FHIRClassMap;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Ox\Interop\Fhir\Resources\CFHIRResource;

/**
 * FHIR data type
 */
class CFHIRDataTypeReference extends CFHIRDataTypeComplex
{
    /** @var string */
    public const REFERENCE_TYPE_CONTAINED = 'contained';
    /** @var string */
    public const REFERENCE_TYPE_ABSOLUTE = 'absolute';
    /** @var string */
    public const REFERENCE_TYPE_RELATIVE = 'relative';

    /** @var string */
    public const NAME = 'Reference';

    /** @var CFHIRDataTypeString */
    public $reference;

    /** @var CFHIRDataTypeUri */
    public $type;

    /** @var CFHIRDataTypeIdentifier */
    public $identifier;

    /** @var CFHIRDataTypeString */
    public $display;

    /** @var CFHIRResource */
    private $_target_resource;

    /**
     * @return string|null
     */
    public function resolveUrl(): ?string
    {
        if ($this->resolveTypeReference() === self::REFERENCE_TYPE_RELATIVE) {
            return $this->reference->getValue();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getReferenceID(): ?string
    {
        $reference_type  = $this->resolveTypeReference();
        $reference_value = $this->reference->getValue();
        if ($reference_type === self::REFERENCE_TYPE_RELATIVE) {
            $parts = explode('/', $reference_value);

            return end($parts);
        }

        if ($reference_type === self::REFERENCE_TYPE_CONTAINED && $reference_value !== null) {
            return str_starts_with($reference_value, '#') ? substr($reference_value, 1) : null;
        }

        if ($reference_type === self::REFERENCE_TYPE_ABSOLUTE && $reference_value !== null) {
            if (($position_id = strrpos($reference_value, '/')) === false) {
                return null;
            }

            return substr($reference_value, $position_id + 1);
        }

        return null;
    }

    /**
     * @return CFHIRResource|null
     */
    public function resolveResourceTarget(): ?CFHIRResource
    {
        if (!$this->reference || !($reference = $this->reference->getValue())) {
            return null;
        }

        $map = new FHIRClassMap();
        switch ($this->resolveTypeReference()) {
            case self::REFERENCE_TYPE_RELATIVE:
                if (!$this->type || !($type = $this->type->getValue())) {
                    preg_match("#(?'type'^\w+)(?:\/)#", $reference, $matches);
                    $type = CMbArray::get($matches, 'type');
                }

                if (!$type) {
                    return null;
                }

                return $map->resource->getResource($type);

            case self::REFERENCE_TYPE_CONTAINED:
                // todo aller chercher dans la resource le contained
                return null;
                break;

            case self::REFERENCE_TYPE_ABSOLUTE:
                return null;
                break;

            default:
                return null;
        }
    }

    public function resolveTypeReference(): ?string
    {
        if (!$this->reference || !($reference = $this->reference->getValue())) {
            return null;
        }

        // contained
        if (str_starts_with($reference, '#')) {
            return self::REFERENCE_TYPE_CONTAINED;
        }

        // absolute
        if (str_starts_with($reference, 'http://') || str_starts_with($reference, 'https://')) {
            return self::REFERENCE_TYPE_ABSOLUTE;
        }

        // relative
        $type = $this->type ? $this->type->getValue() : null;
        if ($type && str_starts_with($reference, "$type/")) {
            return self::REFERENCE_TYPE_RELATIVE;
        } elseif (preg_match("#(?'type'^\w+)(?:\/)#", $reference, $matches)) {
            if (!$type = CMbArray::get($matches, 'type')) {
                return null;
            }

            // try to resolve resource type
            $map = new FHIRClassMap();
            if (!$map->resource->getResource($type)) {
                return null;
            }

            return self::REFERENCE_TYPE_RELATIVE;
        }

        return null;
    }

    /**
     * @return CFHIRResource
     */
    public function getTargetResource(): ?CFHIRResource
    {
        return $this->_target_resource;
    }

    /**
     * @param CFHIRResource $target_resource
     */
    public function setTargetResource(CFHIRResource $target_resource): void
    {
        $this->_target_resource = $target_resource;
    }

    /**
     * @param CFHIRDataTypeString $reference
     *
     * @return CFHIRDataTypeReference
     */
    public function setReference(string $reference): CFHIRDataTypeReference
    {
        $this->reference = new CFHIRDataTypeString($reference);

        return $this;
    }

    /**
     * @param CFHIRDataTypeString $reference
     *
     * @return CFHIRDataTypeReference
     */
    public function setReferenceElement(CFHIRDataTypeString $reference): CFHIRDataTypeReference
    {
        $this->reference = $reference;

        return $this;
    }
}
