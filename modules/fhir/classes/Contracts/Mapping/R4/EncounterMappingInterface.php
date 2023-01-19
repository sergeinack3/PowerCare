<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterClassHistory;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterDiagnosis;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterHospitalization;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterLocation;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Encounter\CFHIRDataTypeEncounterParticipant;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeDuration;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface EncounterMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Encounter";

    /**
     * Map property identifier
     *
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode[]
     */
    public function mapStatusHistory(): array;

    /**
     * Map property class
     *
     * @return CFHIRDataTypeCoding|null
     */
    public function mapClass(): ?CFHIRDataTypeCoding;

    /**
     * Map property class
     *
     * @return CFHIRDataTypeEncounterClassHistory[]
     */
    public function mapClassHistory(): array;

    /**
     * Map property type
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapType(): array;

    /**
     * Map property serviceType
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapServiceType(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property priority
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapPriority(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property subject
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapSubject(): ?CFHIRDataTypeReference;

    /**
     * Map property episodeOfCare
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapEpisodeOfCare(): array;

    /**
     * Map property basedOn
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapBasedOn(): array;

    /**
     * Map property participant
     *
     * @return CFHIRDataTypeEncounterParticipant[]
     */
    public function mapParticipant(): array;

    /**
     * Map property appointment
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapAppointment(): array;

    /**
     * Map property period
     *
     * @return CFHIRDataTypePeriod|null
     */
    public function mapPeriod(): ?CFHIRDataTypePeriod;

    /**
     * Map property length
     *
     * @return CFHIRDataTypeDuration|null
     */
    public function mapLength(): ?CFHIRDataTypeDuration;

    /**
     * Map property reasonCode
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapReasonCode(): array;

    /**
     * Map property reasonReference
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapReasonReference(): array;

    /**
     * Map property account
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapAccount(): array;

    /**
     * Map property serviceProvider
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapServiceProvider(): ?CFHIRDataTypeReference;

    /**
     * Map property partOf
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapPartOf(): ?CFHIRDataTypeReference;

    /**
     * Map property Diagnosis
     *
     * @return CFHIRDataTypeEncounterDiagnosis[]
     */
    public function mapDiagnosis(): array;

    /**
     * Map property Hospitalization
     *
     * @return CFHIRDataTypeEncounterHospitalization|null
     */
    public function mapHospitalization(): ?CFHIRDataTypeEncounterHospitalization;

    /**
     * Map property Location
     *
     * @return CFHIRDataTypeEncounterLocation[]
     */
    public function mapLocation(): array;
}
