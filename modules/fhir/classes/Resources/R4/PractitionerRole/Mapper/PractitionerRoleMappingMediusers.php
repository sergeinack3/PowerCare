<?php
/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\PractitionerRole\Mapper;

use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\PractitionerRoleMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;
use Ox\Mediboard\Mediusers\CMediusers;

class PractitionerRoleMappingMediusers implements DelegatedObjectMapperInterface, PractitionerRoleMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CMediusers */
    protected $object;

    /** @var CFHIRResourcePractitionerRole */
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
        return [CFHIRResourcePractitionerRole::class];
    }

    public function mapActive(): ?CFHIRDataTypeBoolean
    {
        return new CFHIRDataTypeBoolean($this->object->actif);
    }

    public function mapPeriod(): ?CFHIRDataTypePeriod
    {
        $mediuser = $this->object;

        $period = null;
        if ($mediuser->deb_activite && $mediuser->fin_activite) {
            $period = CFHIRDataTypePeriod::from($mediuser->deb_activite, $mediuser->fin_activite);
        }

        return $period;
    }

    public function mapPractitioner(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(CFHIRResourcePractitioner::class, $this->object);
    }

    public function mapOrganization(): ?CFHIRDataTypeReference
    {
        // not implemented
        return null;
    }

    public function mapCode(): array
    {
        $system  = 'urn:oid:2.16.840.1.113883.4.642.3.439';
        $code    = 'doctor';
        $display = 'Doctor';
        $text    = 'A qualified/registered medical practitioner';
        $coding  = CFHIRDataTypeCoding::addCoding($system, $code, $display);

        return [
            CFHIRDataTypeCodeableConcept::addCodeable(
                $coding,
                $text
            ),
        ];
    }

    public function mapSpecialty(): array
    {
        return [];
    }

    public function mapLocation(): array
    {
        // not implemented
        return [];
    }

    public function mapHealthCareService(): array
    {
        // not implemented
        return [];
    }

    public function mapTelecom(): array
    {
        // not implemented
        return [];
    }

    public function mapAvailableTime(): array
    {
        // not implemented
        return [];
    }

    public function mapNotAvailable(): array
    {
        // not implemented
        return [];
    }

    public function mapAvailabilityExceptions(): ?CFHIRDataTypeString
    {
        // not implemented
        return null;
    }

    public function mapEndpoint(): array
    {
        // not implemented
        return [];
    }
}
