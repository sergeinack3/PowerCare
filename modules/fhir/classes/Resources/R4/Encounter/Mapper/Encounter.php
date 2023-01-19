<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Encounter\Mapper;

use Exception;
use Ox\Interop\Eai\CDomain;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\EncounterMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterHospitalization;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterParticipant;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeDuration;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Encounter\CFHIRResourceEncounter;
use Ox\Interop\Fhir\Utilities\Helper\SejourHelper;
use Ox\Mediboard\Patients\CMedecin;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class Encounter implements DelegatedObjectMapperInterface, EncounterMappingInterface
{
    use CStoredObjectResourceDomainTrait {
        mapIdentifier as protected mapIdentifierTrait;
    }

    /** @var CSejour */
    protected $object;

    /** @var CFHIRResourceEncounter */
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
        return [CFHIRResourceEncounter::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CSejour && $object->_id;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function mapIdentifier(): array
    {
        $identifiers = $this->mapIdentifierTrait();
        $domains     = CDomain::loadDomainIdentifiers($this->object);

        /** @var CDomain $_domain */
        foreach ($domains as $_domain) {
            if (empty($sejour->_returned_oids) || in_array($_domain->OID, $sejour->_returned_oids)) {
                $identifier = CFHIRDataTypeIdentifier::build(
                    [
                        "system" => "urn:oid:$_domain->OID",
                        "value"  => $_domain->_identifier->id400,
                    ]
                );

                if ($_domain->isMaster()) {
                    $identifier->type = CFHIRDataTypeCodeableConcept::addCodeable(SejourHelper::getTypeCodingNDA());
                }

                $identifiers[] = $identifier;
            }
        }

        return $identifiers;
    }

    public function mapStatus(): ?CFHIRDataTypeCode
    {
        if ($this->object->annule) {
            return new CFHIRDataTypeCode('cancelled');
        }

        switch ($this->object->_etat) {
            case "preadmission":
                return new CFHIRDataTypeCode('planned');
            case "encours":
                return new CFHIRDataTypeCode('in-progress');
            case "cloture":
                return new CFHIRDataTypeCode('finished');
            default:
                return null;
        }
    }

    public function mapType(): array
    {
        return [];
    }

    public function mapSubject(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(get_class(new CPatient()), $this->object->loadRefPatient());
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapParticipant(): array
    {
        return [
            CFHIRDataTypeEncounterParticipant::build(
                [
                    "type"       => "ADM",
                    "individual" => $this->resource->addReference(
                        get_class(new CMedecin()),
                        $this->object->loadRefMedecinTraitant()
                    ),
                ]
            ),
        ];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     */
    public function mapPeriod(): ?CFHIRDataTypePeriod
    {
        return CFHIRDataTypePeriod::build(
            CFHIRDataTypeInstant::formatPeriod($this->object->entree, $this->object->sortie)
        );
    }

    /**
     * @inheritDoc
     */
    public function mapClass(): ?CFHIRDataTypeCoding
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapServiceType(): ?CFHIRDataTypeCodeableConcept
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapPriority(): ?CFHIRDataTypeCodeableConcept
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapEpisodeOfCare(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapBasedOn(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapAppointment(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapLength(): ?CFHIRDataTypeDuration
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapReasonCode(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapReasonReference(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapAccount(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapServiceProvider(): ?CFHIRDataTypeReference
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
    public function mapStatusHistory(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapClassHistory(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapDiagnosis(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapHospitalization(): ?CFHIRDataTypeEncounterHospitalization
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapLocation(): array
    {
        return [];
    }
}
