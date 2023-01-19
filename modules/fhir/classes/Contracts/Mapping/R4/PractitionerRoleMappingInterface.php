<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Contracts\Mapping\R4;

use Ox\Interop\Fhir\Contracts\Mapping\ResourceDomainMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\PractitionerRole\CFHIRDataTypePractitionerRoleAvailableTime;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\PractitionerRole\CFHIRDataTypePractitionerRolerNotAvailable;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;

/**
 * Description
 */
interface PractitionerRoleMappingInterface extends ResourceDomainMappingInterface
{
    /** @var string */
    public const RESOURCE_TYPE = 'PractitionerRole';

    /**
     * Map property identifier
     *
     * @return CFHIRDataTypeIdentifier[]
     */
    public function mapIdentifier(): array;

    /**
     * Map property active
     */
    public function mapActive(): ?CFHIRDataTypeBoolean;

    /**
     * Map property period
     */
    public function mapPeriod(): ?CFHIRDataTypePeriod;

    /**
     * Map property practitioner
     */
    public function mapPractitioner(): ?CFHIRDataTypeReference;

    /**
     * Map property organization
     */
    public function mapOrganization(): ?CFHIRDataTypeReference;

    /**
     * Map property code
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapCode(): array;

    /**
     * Map property speciality
     *
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function mapSpecialty(): array;

    /**
     * Map property location
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapLocation(): array;

    /**
     * Map property healthcareService
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapHealthCareService(): array;

    /**
     * Map property telecom
     *
     * @return CFHIRDataTypeContactPoint[]
     */
    public function mapTelecom(): array;

    /**
     * Map property availableTime
     *
     * @return CFHIRDataTypePractitionerRoleAvailableTime[]
     */
    public function mapAvailableTime(): array;

    /**
     * Map property notAvailable
     *
     * @return CFHIRDataTypePractitionerRolerNotAvailable[]
     */
    public function mapNotAvailable(): array;

    /**
     * Map property availabilityExceptions
     */
    public function mapAvailabilityExceptions(): ?CFHIRDataTypeString;

    /**
     * Map property endpoint
     *
     * @return CFHIRDataTypeReference[]
     */
    public function mapEndpoint(): array;
}
