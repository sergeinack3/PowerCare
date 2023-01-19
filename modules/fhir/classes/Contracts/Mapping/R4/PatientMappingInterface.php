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
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientCommunication;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientContact;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Patient\CFHIRDataTypePatientLink;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAddress;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAttachment;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeHumanName;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface PatientMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = "Patient";

    /**
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property active
     *
     * @return CFHIRDataTypeBoolean
     */
    public function mapActive(): ?CFHIRDataTypeBoolean;

    /**
     * Map property name
     *
     * @return CFHIRDataTypeHumanName[]
     */
    public function mapName(): array;

    /**
     * Map property telecom
     *
     * @return CFHIRDataTypeContactPoint[]
     */
    public function mapTelecom(): array;

    /**
     * Map property gender
     *
     * @return CFHIRDataTypeCode
     */
    public function mapGender(): ?CFHIRDataTypeCode;

    /**
     * Map property birthDate
     *
     * @return CFHIRDataTypeDate
     */
    public function mapBirthDate(): ?CFHIRDataTypeDate;

    /**
     * Map property deceased
     *
     * @return CFHIRDataTypeChoice
     */
    public function mapDeceased(): ?CFHIRDataTypeChoice;

    /**
     * Map property address
     *
     * @return CFHIRDataTypeAddress[]
     */
    public function mapAddress(): array;

    /**
     * Map property maritalStatus
     *
     * @return CFHIRDataTypeCodeableConcept
     */
    public function mapMaritalStatus(): ?CFHIRDataTypeCodeableConcept;

    /**
     * Map property multipleBirth
     *
     * @return CFHIRDataTypeChoice
     */
    public function mapMultipleBirth(): ?CFHIRDataTypeChoice;

    /**
     * Map property photo
     *
     * @return CFHIRDataTypeAttachment[]
     */
    public function mapPhoto(): array;

    /**
     * Map property contact
     *
     * @return CFHIRDataTypePatientContact[]
     */
    public function mapContact(): array;

    /**
     * Map property communication
     *
     * @return CFHIRDataTypePatientCommunication[]
     */
    public function mapCommunication(): array;

    /**
     * Map property generalPractitioner
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapGeneralPractitioner(string $resource_class): array;

    /**
     * Map property managingOrganization
     *
     * @return CFHIRDataTypeReference
     */
    public function mapManagingOrganization(): ?CFHIRDataTypeReference;

    /**
     * Map property link
     *
     * @return CFHIRDataTypePatientLink[]
     */
    public function mapLink(): array;
}
