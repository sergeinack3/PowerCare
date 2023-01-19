<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\AllergyIntolerance\CFHIRDataTypeAllergyIntoleranceReaction;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface AllergyIntoleranceMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "AllergyIntolerance";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property ClinicalStatus
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapClinicalStatus(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property VerificationStatus
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapVerificationStatus(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property Type
     *
     * @return CFHIRDataTypeCode
     */
    public function mapType(): ?CFHIRDataTypeCode;

    /**
     * Map property Category
     *
     * @return CFHIRDataTypeCode[]
     */
    public function mapCategory(): array;

    /**
     * Map property Criticality
     *
     * @return CFHIRDataTypeCode
     */
    public function mapCriticality(): ?CFHIRDataTypeCode;

    /**
     * Map property Code
     *
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function mapCode(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property Patient
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapPatient(): ?CFHIRDataTypeReference;

    /**
     * Map property Encounter
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapEncounter(): ?CFHIRDataTypeReference;

    /**
     * Map property Onset
     *
     * @return ?CFHIRDataTypeChoice
     */
    public function mapOnset(): ?CFHIRDataTypeChoice;

    /**
     * Map property RecordedDate
     *
     * @return CFHIRDataTypeDateTime|null
     */
    public function mapRecordedDate(): ?CFHIRDataTypeDateTime;

    /**
     * Map property Recorder
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapRecorder(): ?CFHIRDataTypeReference;

    /**
     * Map property Asserter
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapAsserter(): ?CFHIRDataTypeReference;

    /**
     * Map property LastOccurrence
     *
     * @return CFHIRDataTypeDateTime|null
     */
    public function mapLastOccurrence(): ?CFHIRDataTypeDateTime;

    /**
     * Map property end
     *
     * @return CFHIRDataTypeAnnotation[]
     */
    public function mapNote(): array;

    /**
     * Map property minutesDuration
     *
     * @return CFHIRDataTypeAllergyIntoleranceReaction|null
     */
    public function mapReaction(): ?CFHIRDataTypeAllergyIntoleranceReaction;
}
