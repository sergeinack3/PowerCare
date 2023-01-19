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
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeContactPoint;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;
use Ox\Mediboard\Patients\CMedecin;

class PractitionerRoleMappingMedecin implements DelegatedObjectMapperInterface, PractitionerRoleMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CMedecin */
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

    public function mapPeriod(): ?CFHIRDataTypePeriod
    {
        return null;
    }

    public function mapPractitioner(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(CFHIRResourcePractitioner::class, $this->object);
    }

    public function mapOrganization(): ?CFHIRDataTypeReference
    {
        // mapping sur quel exercice_place ? groups current ?
        return null;
    }

    public function mapCode(): array
    {
        $codes   = [];
        $medecin = $this->object;

        // todo gestion des valueSet FHIR
        switch ($medecin->type) {
            case 'medecin':
                $code    = 'doctor';
                $display = $text = 'Doctor';
                break;

            case 'pharmacie':
                $code    = 'pharmacist';
                $display = $text = 'Pharmacist';
                break;

            case 'infirmier':
                $code    = 'nurse';
                $display = $text = 'Nurse';
                break;
            default:
                return $codes;
        }

        $system     = 'http://terminology.hl7.org/CodeSystem/practitioner-role';
        $profession = CFHIRDataTypeCoding::addCoding($system, $code, $display);

        $codes[] = CFHIRDataTypeCodeableConcept::addCodeable($profession, $text);

        return $codes;
    }

    public function mapSpecialty(): array
    {
        return [];
    }

    public function mapLocation(): array
    {
        return [];
    }

    public function mapHealthCareService(): array
    {
        return [];
    }

    public function mapTelecom(): array
    {
        $telecoms = [];
        if ($this->object->tel) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'phone',
                    "value"  => $this->object->tel,
                    'use' => 'work'
                ]
            );
        }

        if ($this->object->fax) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'fax',
                    "value"  => $this->object->fax,
                    'use' => 'work'
                ]
            );
        }

        if ($this->object->email) {
            $telecoms[] = CFHIRDataTypeContactPoint::build(
                [
                    "system" => 'email',
                    "value"  => $this->object->email,
                    'use' => 'work'
                ]
            );
        }

        return $telecoms;
    }

    public function mapAvailableTime(): array
    {
        return [];
    }

    public function mapNotAvailable(): array
    {
        return [];
    }

    public function mapAvailabilityExceptions(): ?CFHIRDataTypeString
    {
        return null;
    }

    public function mapEndpoint(): array
    {
        return [];
    }
}
