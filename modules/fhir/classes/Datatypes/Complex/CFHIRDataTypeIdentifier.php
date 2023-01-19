<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Datatypes\Complex;

use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUri;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * FHIR data type
 */
class CFHIRDataTypeIdentifier extends CFHIRDataTypeComplex
{
    /** @var string */
    public const NAME = 'Identifier';

    /** @var CFHIRDataTypeCode|null */
    public $use;

    /** @var CFHIRDataTypeCodeableConcept */
    public $type;

    /** @var CFHIRDataTypeUri */
    public $system;

    /** @var CFHIRDataTypeString */
    public $value;

    /** @var CFHIRDataTypePeriod */
    public $period;

    /** @var CFHIRDataTypeReference */
    public $assigner;

    public static function makeIdentifier(?string $id, ?string $system = null): self
    {
        $identifier = new self();
        if ($id) {
            $identifier->value = new CFHIRDataTypeString($id);
        }

        if ($system) {
            $identifier->system = new CFHIRDataTypeUri($system);
        }

        return $identifier;
    }

    /**
     * Build identifier field on resource
     *
     * @param string|null                       $identifier identifier
     * @param string|null                       $new_identifier
     * @param CFHIRDataTypeCodeableConcept|null $type       type
     * @param string|null                       $use        use value
     * @param string|null                       $system     system value
     * @param bool                              $merge
     *
     * @return CFHIRDataTypeIdentifier[]
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public static function addIdentifier(
        array $identifier = null,
        ?string $new_identifier = null,
        ?CFHIRDataTypeCodeableConcept $type = null,
        ?string $use = null,
        ?string $system = null,
        bool $merge = true
    ): array {
        if (!$identifier) {
            $identifier = [];
        }

        $data = self::build(
            [
                'type'   => $type ?: null,
                'use'    => $use ? new CFHIRDataTypeCode($use) : null,
                'system' => $system ? new CFHIRDataTypeUri($system) : null,
                'value'  => $new_identifier ? new CFHIRDataTypeString($new_identifier) : null,
            ]
        );

        if (!$merge) {
            return [$data];
        }

        return array_merge($identifier, [$data]);
    }

    /**
     * @param string $system
     *
     * @return bool
     */
    public function isSystemMatch(string $system): bool
    {
        if ($this->system && !$this->system->isNull()) {
            return $this->system->isSystemMatch($system);
        }

        return false;
    }

    /**
     * @param CFHIRDataTypeCode $use
     *
     * @return CFHIRDataTypeIdentifier
     */
    public function setUse(CFHIRDataTypeCode $use): CFHIRDataTypeIdentifier
    {
        $this->use = $use;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept $type
     *
     * @return CFHIRDataTypeIdentifier
     */
    public function setType(CFHIRDataTypeCodeableConcept $type): CFHIRDataTypeIdentifier
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string|mixed $value
     *
     * @return CFHIRDataTypeContactPoint
     */
    public function setValue($value): self
    {
        $this->value = new CFHIRDataTypeString($value);

        return $this;
    }

    /**
     * @param CFHIRDataTypeUri $system
     *
     * @return CFHIRDataTypeIdentifier
     */
    public function setSystem(string $system): CFHIRDataTypeIdentifier
    {
        $this->system = $system;

        return $this;
    }

    /**
     * @param CFHIRDataTypeUri $system
     *
     * @return CFHIRDataTypeIdentifier
     */
    public function setSystemElement(CFHIRDataTypeUri $system): CFHIRDataTypeIdentifier
    {
        $this->system = $system;

        return $this;
    }
}
