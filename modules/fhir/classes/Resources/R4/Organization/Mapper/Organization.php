<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Organization\Mapper;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\OrganizationMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Organization\CFHIRResourceOrganization;
use Ox\Mediboard\Patients\CExercicePlace;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class Organization implements DelegatedObjectMapperInterface, OrganizationMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CExercicePlace */
    protected $object;

    /** @var CFHIRResourceOrganization */
    protected CFHIRResource $resource;

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
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return void
     */
    public function setResource(CFHIRResource $resource,  $object): void
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
        return [CFHIRResourceOrganization::class];
    }

    public function mapActive(): ?CFHIRDataTypeBoolean
    {
        return new CFHIRDataTypeBoolean(true);
    }

    public function mapType(): array
    {
        $system      = 'urn:oid:2.16.840.1.113883.4.642.1.1128';
        $code        = 'prov';
        $displayName = 'Healthcare Provider';
        $coding      = CFHIRDataTypeCoding::addCoding($system, $code, $displayName);
        $text        = 'An organization that provides healthcare services.';

        return [CFHIRDataTypeCodeableConcept::addCodeable($coding, $text)];
    }

    public function mapName(): ?CFHIRDataTypeString
    {
        return new CFHIRDataTypeString($this->object->raison_sociale);
    }

    public function mapAlias(): array
    {
        return [new CFHIRDataTypeString($this->object->raison_sociale)];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
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
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapAddress(): array
    {
        if ($this->object->adresse || $this->object->commune || $this->object->cp || $this->object->pays) {
            return [
                CFHIRDataTypeAddress::build(
                    [
                        'use'        => new CFHIRDataTypeCode('work'),
                        'type'       => new CFHIRDataTypeCode('both'),
                        'line'       => new CFHIRDataTypeString($this->object->adresse) ?? null,
                        'city'       => new CFHIRDataTypeString($this->object->commune) ?? null,
                        'postalCode' => new CFHIRDataTypeString($this->object->cp) ?? null,
                        'country'    => new CFHIRDataTypeString($this->object->pays) ?? null,
                    ]
                ),
            ];
        }

        return [];
    }

    public function mapPartOf(): ?CFHIRDataTypeReference
    {
        // not implemented
        return null;
    }

    public function mapContact(): array
    {
        // not implemented
        return [];
    }

    public function mapEndpoint(): array
    {
        // not implemented
        return [];
    }
}
