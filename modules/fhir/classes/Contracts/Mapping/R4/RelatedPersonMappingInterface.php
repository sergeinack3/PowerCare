<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDate;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\RelatedPerson\CFHIRDataTypeRelatedPersonCommunication;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface RelatedPersonMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "RelatedPerson";

    /**
     * Map property identifier
     *
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property active
     *
     * @return CFHIRDataTypeBoolean|null
     */
    public function mapActive(): ?CFHIRDataTypeBoolean;

    /**
     * Map property patient
     *
     * @return CFHIRDataTypeReference|null
     */
    public function mapPatient(): ?CFHIRDataTypeReference;

    /**
     * Map property Relationship
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapRelationship(): array;

    /**
     * Map property Name
     *
     * @return CFHIRDataTypeHumanName[]
     */
    public function mapName(): array;

    /**
     * Map property Telecom
     *
     * @return CFHIRDataTypeContactPoint[]
     */
    public function mapTelecom(): array;

    /**
     * Map property Gender
     *
     * @return CFHIRDataTypeCode|null
     */
    public function mapGender(): ?CFHIRDataTypeCode;

    /**
     * Map property BirthDate
     *
     * @return CFHIRDataTypeDate|null
     */
    public function mapBirthDate(): ?CFHIRDataTypeDate;

    /**
     * Map property Address
     *
     * @return CFHIRDataTypeAddress[]
     */
    public function mapAddress(): array;

    /**
     * Map property Photo
     *
     * @return CFHIRDataTypeAttachment[]
     */
    public function mapPhoto(): array;

    /**
     * Map property Period
     *
     * @return CFHIRDataTypePeriod|null
     */
    public function mapPeriod(): ?CFHIRDataTypePeriod;

    /**
     * Map property Communication
     *
     * @return CFHIRDataTypeRelatedPersonCommunication[]
     */
    public function mapCommunication(): array;
}
