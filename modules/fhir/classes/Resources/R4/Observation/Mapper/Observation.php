<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation\Mapper;

use Exception;
use Ox\Core\CAppUI;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\ObservationMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeIdentifier;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Device\CFHIRResourceDevice;
use Ox\Interop\Fhir\Resources\R4\Observation\CFHIRResourceObservation;
use Ox\Mediboard\Patients\Constants\CAbstractConstant;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Description
 */
class Observation implements DelegatedObjectMapperInterface, ObservationMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CAbstractConstant */
    protected $object;

    /** @var CFHIRResourceObservation */
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
     * @inheritDoc
     */
    public function onlyProfiles(): array
    {
        return [CFHIR::class];
    }

    /**
     * @inheritDoc
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceObservation::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CAbstractConstant && $object->_id;
    }

    /**
     * @inheritDoc
     */
    public function mapBasedOn(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapPartOf(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapStatus(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode('final');
    }

    /**
     * @inheritDoc
     */
    public function mapCategory(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $system  = "http://loinc.org";
        $code    = "34566-0";
        $display = "Vital signs with method details panel";

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);
        $text   = "Vital signs with method details panel";

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
    }

    /**
     * @inheritDoc
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function mapSubject(): ?CFHIRDataTypeReference
    {
        $patient = $this->object->loadRefPatient();

        $reference = CFHIRDataTypeReference::build([
                                                       "identifier" => CFHIRDataTypeIdentifier::addIdentifier(
                                                           null,
                                                           CAppUI::conf(
                                                               'mb_oid'
                                                           ) . '|' . $patient->loadIPP()
                                                       )[0],
                                                   ]);

        return $reference;
    }

    /**
     * @inheritDoc
     */
    public function mapFocus(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapEncounter(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapEffective(): ?CFHIRDataTypeChoice
    {
        return new CFHIRDataTypeChoice(CFHIRDataTypeDateTime::class, $this->object->datetime);
    }

    /**
     * @inheritDoc
     */
    public function mapIssued(): ?CFHIRDataTypeInstant
    {
        return null;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function mapPerformer(): array
    {
        $patient = $this->object->loadRefPatient();

        $reference = CFHIRDataTypeReference::build([
                                                       "identifier" => CFHIRDataTypeIdentifier::addIdentifier(
                                                           null,
                                                           CAppUI::conf(
                                                               'mb_oid'
                                                           ) . '|' . $patient->loadIPP()
                                                       )[0],
                                                   ]);

        return [$reference];
    }

    /**
     * @inheritDoc
     */
    public function mapValue(): ?CFHIRDataTypeChoice
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapDataAbsentReason(): ?CFHIRDataTypeCodeableConcept
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapInterpretation(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapNote(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapBodySite(): ?CFHIRDataTypeCodeableConcept
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapMethod(): ?CFHIRDataTypeCodeableConcept
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapSpecimen(): ?CFHIRDataTypeReference
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapDevice(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(CFHIRResourceDevice::class, $this->object);
    }

    /**
     * @inheritDoc
     */
    public function mapReferenceRange(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapHasMember(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapDerivedFrom(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function mapComponent(): array
    {
        return [];
    }
}
