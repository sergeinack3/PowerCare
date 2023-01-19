<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Slot\Mapper;

use Exception;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\SlotMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Schedule\CFHIRResourceSchedule;
use Ox\Interop\Fhir\Resources\R4\Slot\CFHIRResourceSlot;
use Ox\Mediboard\Cabinet\CSlot;

/**
 * Description
 */
class Slot implements DelegatedObjectMapperInterface, SlotMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CSlot */
    protected $object;

    /** @var CFHIRResourceSlot */
    protected CFHIRResource $resource;

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return void
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->object   = $object;
        $this->resource = $resource;
    }

    /**
     * @return string[]
     */
    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceSlot::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CSlot && $object->_id;
    }

    public function mapServiceCategory(): array
    {
        $system      = 'http://terminology.hl7.org/CodeSystem/service-category';
        $code        = '35';
        $displayName = 'Hospital';
        $coding      = CFHIRDataTypeCoding::addCoding($system, $code, $displayName);
        $text        = 'Hospital';

        return [CFHIRDataTypeCodeableConcept::addCodeable($coding, $text)];
    }

    public function mapServiceType(): array
    {
        // not implemented
        return [];
    }

    /**
     * @throws Exception
     */
    public function mapSpecialty(): array
    {
        return [];
    }

    public function mapAppointmentType(): ?CFHIRDataTypeCodeableConcept
    {
        // not implemented
        return null;
    }

    public function mapSchedule(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(CFHIRResourceSchedule::class, $this->object);
    }

    public function mapStatus(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode($this->object->status);
    }

    public function mapStart(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->start);
    }

    public function mapEnd(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->end);
    }

    public function mapOverbooked(): ?CFHIRDataTypeBoolean
    {
        return new CFHIRDataTypeBoolean($this->object->overbooked);
    }

    public function mapComment(): ?CFHIRDataTypeString
    {
        // not implemented
        return null;
    }
}
