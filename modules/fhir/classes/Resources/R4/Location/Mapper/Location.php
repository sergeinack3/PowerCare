<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Location\Mapper;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\LocationMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Location\CFHIRDataTypeLocationPosition;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Location\CFHIRResourceLocation;
use Ox\Mediboard\Patients\CExercicePlace;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class Location implements DelegatedObjectMapperInterface, LocationMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CExercicePlace */
    protected $object;

    /** @var CFHIRResourceLocation */
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
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CExercicePlace && $object->_id;
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
        return [CFHIRResourceLocation::class];
    }

    /**
     * @inheritDoc
     */
    public function mapStatus(): ?CFHIRDataTypeCode
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapOperationalStatus(): ?CFHIRDataTypeCoding
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapName(): ?CFHIRDataTypeString
    {
        return new CFHIRDataTypeString($this->object->raison_sociale);
    }

    /**
     * @inheritDoc
     */
    public function mapAlias(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapDescription(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapMode(): ?CFHIRDataTypeCode
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapType(): array
    {
        return [];
    }

    /**
     * @return array|CFHIRDataTypeContactPoint[]
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function mapTelecom(): array
    {
        $telecoms = [];
        if ($this->object->tel) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    'system' => 'phone',
                    'value'  => $this->object->tel,
                ]
            );
        }

        if ($this->object->tel2) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    'system' => 'phone',
                    'value'  => $this->object->tel2,
                ]
            );
        }

        if ($this->object->fax) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    'system' => 'phone',
                    'value'  => $this->object->fax,
                ]
            );
        }

        if ($this->object->email) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    'system' => 'email',
                    'value'  => $this->object->email,
                ]
            );
        }

        return $telecoms;
    }

    /**
     * @return CFHIRDataTypeAddress|null
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function mapAddress(): ?CFHIRDataTypeAddress
    {
        return CFHIRDataTypeAddress::build([
                                               'type'       => new CFHIRDataTypeCode('both'),
                                               'line'       => new CFHIRDataTypeString($this->object->adresse),
                                               'city'       => new CFHIRDataTypeString($this->object->commune),
                                               'postalCode' => new CFHIRDataTypeString($this->object->cp),
                                               'country'    => new CFHIRDataTypeString($this->object->pays),
                                           ]);
    }

    /**
     * @inheritDoc
     */
    public function mapPhysicalType(): ?CFHIRDataTypeCodeableConcept
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapPosition(): ?CFHIRDataTypeLocationPosition
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapManagingOrganization(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapPartOf(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapHoursOfOperation(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapAvailabilityExceptions(): ?CFHIRDataTypeString
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapEndpoint(): array
    {
        return [];
    }
}
