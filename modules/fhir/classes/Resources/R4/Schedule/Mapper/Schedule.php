<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Schedule\Mapper;

use Exception;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\ScheduleMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeBoolean;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Resources\R4\Schedule\CFHIRResourceSchedule;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Patients\CMedecin;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class Schedule implements DelegatedObjectMapperInterface, ScheduleMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CPlageconsult */
    protected $object;

    /** @var CFHIRResourceSchedule */
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

    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    public function onlyRessources(): array
    {
        return [CFHIRResourceSchedule::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param               $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CPlageconsult && $object->_id;
    }

    public function mapActive(): ?CFHIRDataTypeBoolean
    {
        return new CFHIRDataTypeBoolean(!$this->object->locked);
    }

    public function mapServiceCategory(): array
    {
        $system      = 'http://terminology.hl7.org/CodeSystem/service-category';
        $code        = '35';
        $displayName = 'Hospital';
        $coding      = CFHIRDataTypeCoding::addCoding($system, $code, $displayName);
        $text        = 'Hospital';

        return [CFHIRDataTypeCodeableConcept::addCodeable($coding, $text)];
    }

    public function mapServiceType(): array
    {
        // not implemented
        return [];
    }

    public function mapSpecialty(): array
    {
        return [];
    }

    /**
     * @throws Exception
     */
    public function mapActor(): array
    {
        // Les actors sont à dynamiser en fonction du besoin, pour le projectathon nous avons échange un practitioner
        $practitioner  = $this->object->loadRefChir();
        $medecin       = new CMedecin();
        $medecin->rpps = $practitioner->rpps;
        $medecin->loadMatchingObject();

        return [$this->resource->addReference(CFHIRResourcePractitioner::class, $medecin)];
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function mapPlanningHorizon(): ?CFHIRDataTypePeriod
    {
        return CFHIRDataTypePeriod::build(
            CFHIRDataTypeInstant::formatPeriod(
                CFHIR::getTimeUtc($this->object->debut, false),
                CFHIR::getTimeUtc($this->object->fin, false)
            )
        );
    }

    public function mapComment(): ?CFHIRDataTypeString
    {
        // not implemented
        return null;
    }
}
