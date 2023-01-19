<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation\CFHIRDataTypeObservationComponent;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface ObservationMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Observation";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property basedOn
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapBasedOn(): array;

    /**
     * Map property partOf
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapPartOf(): array;

    /**
     * Map property status
     *
     * @return CFHIRDataTypeCode
     */
    public function mapStatus(): ?CFHIRDataTypeCode;

    /**
     * Map property category
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapCategory(): array;

    /**
     * Map property code
     *
     * @return CFHIRDataTypeCodeableConcept
     */
    public function mapCode(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property subject
     *
     * @return CFHIRDataTypeReference
     */
    public function mapSubject(): ?CFHIRDataTypeReference;

    /**
     * Map property focus
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapFocus(): array;

    /**
     * Map property encounter
     *
     * @return CFHIRDataTypeReference
     */
    public function mapEncounter(): ?CFHIRDataTypeReference;

    /**
     * Map property effective
     *
     * @return CFHIRDataTypeChoice
     */
    public function mapEffective(): ?CFHIRDataTypeChoice;

    /**
     * Map property issued
     *
     * @return CFHIRDataTypeInstant
     */
    public function mapIssued(): ?CFHIRDataTypeInstant;

    /**
     * Map property performer
     *
     * @return CFHIRDataTypeReference
     */
    public function mapPerformer(): array;

    /**
     * Map property value
     *
     * @return CFHIRDataTypeChoice
     */
    public function mapValue(): ?CFHIRDataTypeChoice;

    /**
     * Map property dataAbsentReason
     *
     * @return CFHIRDataTypeCodeableConcept
     */
    public function mapDataAbsentReason(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property interpretation
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapInterpretation(): array;

    /**
     * Map property note
     *
     * @return CFHIRDataTypeAnnotation[]
     */
    public function mapNote(): array;

    /**
     * Map property bodySite
     *
     * @return CFHIRDataTypeCodeableConcept
     */
    public function mapBodySite(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property method
     *
     * @return CFHIRDataTypeCodeableConcept
     */
    public function mapMethod(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property specimen
     *
     * @return CFHIRDataTypeReference
     */
    public function mapSpecimen(): ?CFHIRDataTypeReference;

    /**
     * Map property device
     *
     * @return CFHIRDataTypeReference
     */
    public function mapDevice(): ?CFHIRDataTypeReference;

    /**
     * Map property referenceRange
     *
     * @return CFHIRDataTypeBackboneElement[]
     */
    public function mapReferenceRange(): array;

    /**
     * Map property hasMember
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapHasMember(): array;

    /**
     * Map property derivedFrom
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapDerivedFrom(): array;

    /**
     * Map property component
     *
     * @return CFHIRDataTypeObservationComponent[]
     */
    public function mapComponent(): array;
}
