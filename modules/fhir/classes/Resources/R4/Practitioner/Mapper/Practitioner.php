<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Practitioner\Mapper;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\PractitionerMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Practitioner\CFHIRDataTypePractitionerQualification;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Mediboard\Patients\CMedecin;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class Practitioner implements DelegatedObjectMapperInterface, PractitionerMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CMedecin */
    protected $object;

    /** @var CFHIRResourcePractitioner */
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

    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourcePractitioner::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CMedecin && $object->_id;
    }

    public function mapActive(): ?CFHIRDataTypeBoolean
    {
        return new CFHIRDataTypeBoolean($this->object->actif);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function mapName(): array
    {
        return CFHIRDataTypeHumanName::addName(
            $this->object->nom,
            $this->object->prenom,
            'usual',
            $this->object->_view
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function mapTelecom(): array
    {
        $telecoms = [];
        if ($this->object->tel) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "phone",
                    "value"  => $this->object->tel,
                ]
            );
        }

        if ($this->object->tel_autre) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "phone",
                    "value"  => $this->object->tel_autre,
                ]
            );
        }

        if ($this->object->fax) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "fax",
                    "value"  => $this->object->fax,
                ]
            );
        }

        if ($this->object->portable) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "phone",
                    "value"  => $this->object->portable,
                ]
            );
        }

        if ($this->object->email) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => "email",
                    "value"  => $this->object->email,
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
        if ($this->object->adresse || $this->object->ville || $this->object->cp) {
            return [
                CFHIRDataTypeAddress::build(
                    [
                        'use'        => 'work',
                        'type'       => 'postal',
                        'line'       => preg_split('/[\r\n]+/', $this->object->adresse) ?? null,
                        'city'       => $this->object->ville ?? null,
                        'postalCode' => $this->object->cp ?? null,
                    ]
                ),
            ];
        }

        return [];
    }

    public function mapGender(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode($this->resource->formatGender($this->object->sexe));
    }

    public function mapBirthDate(): ?CFHIRDataTypeDate
    {
        return null;
    }

    public function mapPhoto(): array
    {
        return [];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapQualification(): array
    {
        $system  = 'urn:oid:2.16.840.1.113883.18.220';
        $code    = 'MD';
        $display = 'Doctor of Medicine';
        $text    = 'Doctor of Medicine';

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);

        return [
            CFHIRDataTypePractitionerQualification::build(
                [
                    'identifier' => null,
                    'code'       => CFHIRDataTypeCodeableConcept::addCodeable($coding, $text),
                    'period'     => null,
                    'issuer'     => null,
                ]
            ),
        ];
    }

    public function mapCommunication(): array
    {
        $system  = 'urn:ietf:bcp:47';
        $code    = 'fr-FR';
        $display = 'French (France)';
        $coding  = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text    = 'Français de France';

        return [
            CFHIRDataTypeCodeableConcept::addCodeable(
                $coding,
                $text
            ),
        ];
    }
}
