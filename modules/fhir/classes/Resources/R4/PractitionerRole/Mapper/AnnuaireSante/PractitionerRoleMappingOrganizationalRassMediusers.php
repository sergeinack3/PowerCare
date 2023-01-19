<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\PractitionerRole\Mapper\AnnuaireSante;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\PractitionerRoleMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIRAnnuaireSante;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\Profiles\AnnuaireSante\CFHIRResourcePractitionerRoleOrganizationalRass;
use Ox\Mediboard\Mediusers\CMediusers;

class PractitionerRoleMappingOrganizationalRassMediusers implements DelegatedObjectMapperInterface, PractitionerRoleMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CMediusers */
    protected $object;

    /** @var CFHIRResourcePractitionerRoleOrganizationalRass */
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
        return [CFHIRAnnuaireSante::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourcePractitionerRoleOrganizationalRass::class];
    }

    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CMediusers && $object->_id;
    }

    public function mapActive(): ?CFHIRDataTypeBoolean
    {
        // TODO: Implement mapActive() method.
        return null;
    }

    public function mapPeriod(): ?CFHIRDataTypePeriod
    {
        // TODO: Implement mapPeriod() method.
        return null;
    }

    public function mapPractitioner(): ?CFHIRDataTypeReference
    {
        // TODO: Implement mapPractitioner() method.
        return null;
    }

    public function mapOrganization(): ?CFHIRDataTypeReference
    {
        // TODO: Implement mapOrganization() method.
        return null;
    }

    public function mapCode(): array
    {
        // TODO: Implement mapCode() method.
        return [];
    }

    public function mapSpecialty(): array
    {
        // TODO: Implement mapSpecialty() method.
        return [];
    }

    public function mapLocation(): array
    {
        // TODO: Implement mapLocation() method.
        return [];
    }

    public function mapHealthCareService(): array
    {
        // TODO: Implement mapHealthCareService() method.
        return [];
    }

    public function mapTelecom(): array
    {
        // TODO: Implement mapTelecom() method.
        return [];
    }

    public function mapAvailableTime(): array
    {
        // TODO: Implement mapAvailableTime() method.
        return [];
    }

    public function mapNotAvailable(): array
    {
        // TODO: Implement mapNotAvailable() method.
        return [];
    }

    public function mapAvailabilityExceptions(): ?CFHIRDataTypeString
    {
        // TODO: Implement mapAvailabilityExceptions() method.
        return null;
    }

    public function mapEndpoint(): array
    {
        // TODO: Implement mapEndpoint() method.
        return [];
    }
}
