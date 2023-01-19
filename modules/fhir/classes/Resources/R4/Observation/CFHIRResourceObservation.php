<?php

/**
 * @package Mediboard\Fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Observation;

use Exception;
use Ox\Interop\Fhir\Contracts\Mapping\R4\ObservationMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\CFHIRDataTypeBackboneElement;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation\CFHIRDataTypeObservationComponent;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Observation\CFHIRDataTypeObservationReferenceRange;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * FHIR observation resource
 */
class CFHIRResourceObservation extends CFHIRDomainResource
{
    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'Observation';

    // attributes
    /** @var CFHIRDataTypeReference[] */
    protected array $basedOn = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $partOf = [];

    protected ?CFHIRDataTypeCode $status = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $category = [];

    protected ?CFHIRDataTypeCodeableConcept $code = null;

    protected ?CFHIRDataTypeReference $subject = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $focus = [];

    protected ?CFHIRDataTypeReference $encounter = null;

    protected ?CFHIRDataType $effective = null;

    protected ?CFHIRDataTypeInstant $issued = null;

    /** @var CFHIRDataTypeReference[] */
    protected array $performer = [];

    /** @var CFHIRDataType|null */
    protected ?CFHIRDataType $value = null;

    protected ?CFHIRDataTypeCodeableConcept $dataAbsentReason = null;

    /** @var CFHIRDataTypeCodeableConcept[] */
    protected array $interpretation = [];

    /** @var CFHIRDataTypeAnnotation[] */
    protected array $note = [];

    protected ?CFHIRDataTypeCodeableConcept $bodySite = null;

    protected ?CFHIRDataTypeCodeableConcept $method = null;

    protected ?CFHIRDataTypeReference $specimen = null;

    protected ?CFHIRDataTypeReference $device = null;

    /** @var CFHIRDataTypeObservationReferenceRange[] */
    protected array $referenceRange = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $hasMember = [];

    /** @var CFHIRDataTypeReference[] */
    protected array $derivedFrom = [];

    /** @var CFHIRDataTypeObservationComponent[] */
    protected array $component = [];

    /** @var ObservationMappingInterface */
    protected $object_mapping;

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getBasedOn(): array
    {
        return $this->basedOn;
    }

    /**
     * @param CFHIRDataTypeReference ...$basedOn
     */
    public function setBasedOn(CFHIRDataTypeReference ...$basedOn): self
    {
        $this->basedOn = $basedOn;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$basedOn
     */
    public function addBasedOn(CFHIRDataTypeReference ...$basedOn): self
    {
        $this->basedOn = array_merge($this->basedOn, $basedOn);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getPartOf(): array
    {
        return $this->partOf;
    }

    /**
     * @param CFHIRDataTypeReference ...$partOf
     */
    public function setPartOf(CFHIRDataTypeReference ...$partOf): self
    {
        $this->partOf = $partOf;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$partOf
     */
    public function addPartOf(CFHIRDataTypeReference ...$partOf): self
    {
        $this->partOf = array_merge($this->partOf, $partOf);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getStatus(): ?CFHIRDataTypeCode
    {
        return $this->status;
    }

    /**
     * @param CFHIRDataTypeCode|null $status
     *
     * @return CFHIRResourceObservation
     */
    public function setStatus(?CFHIRDataTypeCode $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getCategory(): array
    {
        return $this->category;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$category
     */
    public function setCategory(CFHIRDataTypeCodeableConcept ...$category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$category
     */
    public function addCategory(CFHIRDataTypeCodeableConcept ...$category): self
    {
        $this->category = array_merge($this->category, $category);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getCode(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->code;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $code
     *
     * @return CFHIRResourceObservation
     */
    public function setCode(?CFHIRDataTypeCodeableConcept $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getSubject(): ?CFHIRDataTypeReference
    {
        return $this->subject;
    }

    /**
     * @param CFHIRDataTypeReference|null $subject
     *
     * @return CFHIRResourceObservation
     */
    public function setSubject(?CFHIRDataTypeReference $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getFocus(): array
    {
        return $this->focus;
    }

    /**
     * @param CFHIRDataTypeReference ...$focus
     */
    public function setFocus(CFHIRDataTypeReference ...$focus): self
    {
        $this->focus = $focus;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$focus
     */
    public function addFocus(CFHIRDataTypeReference ...$focus): self
    {
        $this->focus = array_merge($this->focus, $focus);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getEncounter(): ?CFHIRDataTypeReference
    {
        return $this->encounter;
    }

    /**
     * @param CFHIRDataTypeReference|null $encounter
     *
     * @return CFHIRResourceObservation
     */
    public function setEncounter(?CFHIRDataTypeReference $encounter): self
    {
        $this->encounter = $encounter;

        return $this;
    }

    /**
     * @return CFHIRDataType|null
     */
    public function getEffective(): ?CFHIRDataType
    {
        return $this->effective;
    }

    /**
     * @param CFHIRDataType|null $effective
     *
     * @return CFHIRResourceObservation
     */
    public function setEffective(?CFHIRDataType $effective): self
    {
        $this->effective = $effective;

        return $this;
    }

    /**
     * @return CFHIRDataTypeInstant|null
     */
    public function getIssued(): ?CFHIRDataTypeInstant
    {
        return $this->issued;
    }

    /**
     * @param CFHIRDataTypeInstant|null $issued
     *
     * @return CFHIRResourceObservation
     */
    public function setIssued(?CFHIRDataTypeInstant $issued): self
    {
        $this->issued = $issued;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getPerformer(): array
    {
        return $this->performer;
    }

    /**
     * @param CFHIRDataTypeReference ...$performer
     */
    public function setPerformer(CFHIRDataTypeReference ...$performer): self
    {
        $this->performer = $performer;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$performer
     */
    public function addPerformer(CFHIRDataTypeReference ...$performer): self
    {
        $this->performer = array_merge($this->performer, $performer);

        return $this;
    }

    /**
     * @return CFHIRDataType|null
     */
    public function getValue(): ?CFHIRDataType
    {
        return $this->value;
    }

    /**
     * @param CFHIRDataType $value
     */
    public function setValue(?CFHIRDataType $value): self
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getDataAbsentReason(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->dataAbsentReason;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept $dataAbsentReason
     */
    public function setDataAbsentReason(?CFHIRDataTypeCodeableConcept $dataAbsentReason): self
    {
        $this->dataAbsentReason = $dataAbsentReason;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept[]
     */
    public function getInterpretation(): array
    {
        return $this->interpretation;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$interpretation
     *
     * @return CFHIRResourceObservation
     */
    public function setInterpretation(CFHIRDataTypeCodeableConcept ...$interpretation): self
    {
        $this->interpretation = $interpretation;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept ...$interpretation
     *
     * @return CFHIRResourceObservation
     */
    public function addInterpretation(CFHIRDataTypeCodeableConcept ...$interpretation): self
    {
        $this->interpretation = array_merge($this->interpretation, $interpretation);

        return $this;
    }

    /**
     * @return CFHIRDataTypeAnnotation[]
     */
    public function getNote(): array
    {
        return $this->note;
    }

    /**
     * @param CFHIRDataTypeAnnotation ...$note
     *
     * @return CFHIRResourceObservation
     */
    public function setNote(CFHIRDataTypeAnnotation ...$note): self
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAnnotation ...$note
     *
     * @return CFHIRResourceObservation
     */
    public function addNote(CFHIRDataTypeAnnotation ...$note): self
    {
        $this->note = array_merge($this->note, $note);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getBodySite(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->bodySite;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $bodySite
     *
     * @return CFHIRResourceObservation
     */
    public function setBodySite(?CFHIRDataTypeCodeableConcept $bodySite): self
    {
        $this->bodySite = $bodySite;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getMethod(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->method;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $method
     *
     * @return CFHIRResourceObservation
     */
    public function setMethod(?CFHIRDataTypeCodeableConcept $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getSpecimen(): ?CFHIRDataTypeReference
    {
        return $this->specimen;
    }

    /**
     * @param CFHIRDataTypeReference $specimen
     */
    public function setSpecimen(?CFHIRDataTypeReference $specimen): self
    {
        $this->specimen = $specimen;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getDevice(): ?CFHIRDataTypeReference
    {
        return $this->device;
    }

    /**
     * @param CFHIRDataTypeReference $device
     */
    public function setDevice(?CFHIRDataTypeReference $device): self
    {
        $this->device = $device;

        return $this;
    }

    /**
     * @return CFHIRDataTypeBackboneElement[]
     */
    public function getReferenceRange(): array
    {
        return $this->referenceRange;
    }

    /**
     * @param CFHIRDataTypeBackboneElement ...$referenceRange
     *
     * @return CFHIRResourceObservation
     */
    public function setReferenceRange(CFHIRDataTypeBackboneElement ...$referenceRange): self
    {
        $this->referenceRange = $referenceRange;

        return $this;
    }

    /**
     * @param CFHIRDataTypeBackboneElement ...$referenceRange
     *
     * @return CFHIRResourceObservation
     */
    public function addReferenceRange(CFHIRDataTypeBackboneElement ...$referenceRange): self
    {
        $this->referenceRange = array_merge($this->referenceRange, $referenceRange);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getHasMember(): array
    {
        return $this->hasMember;
    }

    /**
     * @param CFHIRDataTypeReference ...$hasMember
     *
     * @return CFHIRResourceObservation
     */
    public function setHasMember(CFHIRDataTypeReference ...$hasMember): self
    {
        $this->hasMember = $hasMember;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$hasMember
     *
     * @return CFHIRResourceObservation
     */
    public function addHasMember(CFHIRDataTypeReference ...$hasMember): self
    {
        $this->hasMember = array_merge($this->hasMember, $hasMember);

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference[]
     */
    public function getDerivedFrom(): array
    {
        return $this->derivedFrom;
    }

    /**
     * @param CFHIRDataTypeReference ...$derivedFrom
     *
     * @return CFHIRResourceObservation
     */
    public function setDerivedFrom(CFHIRDataTypeReference ...$derivedFrom): self
    {
        $this->derivedFrom = $derivedFrom;

        return $this;
    }

    /**
     * @param CFHIRDataTypeReference ...$derivedFrom
     *
     * @return CFHIRResourceObservation
     */
    public function addDerivedFrom(CFHIRDataTypeReference ...$derivedFrom): self
    {
        $this->derivedFrom = array_merge($this->derivedFrom, $derivedFrom);

        return $this;
    }

    /**
     * @return CFHIRDataTypeObservationComponent[]
     */
    public function getComponent(): array
    {
        return $this->component;
    }

    /**
     * @param CFHIRDataTypeObservationComponent ...$component
     *
     * @return CFHIRResourceObservation
     */
    public function setComponent(CFHIRDataTypeObservationComponent ...$component): self
    {
        $this->component = $component;

        return $this;
    }

    /**
     * @param CFHIRDataTypeObservationComponent ...$component
     *
     * @return CFHIRResourceObservation
     */
    public function addComponent(CFHIRDataTypeObservationComponent ...$component): self
    {
        $this->component = array_merge($this->component, $component);

        return $this;
    }

    /**
     * @return CCapabilitiesResource
     */
    public function generateCapabilities(): CCapabilitiesResource
    {
        return (parent::generateCapabilities())
            ->addInteractions(
                [
                    CFHIRInteractionCreate::NAME,
                    CFHIRInteractionRead::NAME,
                    CFHIRInteractionSearch::NAME,
                    CFHIRInteractionUpdate::NAME,
                    CFHIRInteractionDelete::NAME,
                ]
            );
    }

    /**
     * Map property basedOn
     */
    protected function mapBasedOn(): void
    {
        $this->basedOn = $this->object_mapping->mapBasedOn();
    }

    /**
     * Map property partOf
     */
    protected function mapPartOf(): void
    {
        $this->partOf = $this->object_mapping->mapPartOf();
    }

    /**
     * Map property status
     */
    protected function mapStatus(): void
    {
        $this->status = $this->object_mapping->mapStatus();
    }

    /**
     * Map property category
     */
    protected function mapCategory(): void
    {
        $this->category = $this->object_mapping->mapCategory();
    }

    /**
     * Map property code
     */
    protected function mapCode(): void
    {
        $this->code = $this->object_mapping->mapCode();
    }

    /**
     * Map property subject
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function mapSubject(): void
    {
        $this->subject = $this->object_mapping->mapSubject();
    }

    /**
     * Map property focus
     */
    protected function mapFocus(): void
    {
        $this->focus = $this->object_mapping->mapFocus();
    }

    /**
     * Map property encounter
     */
    protected function mapEncounter(): void
    {
        $this->encounter = $this->object_mapping->mapEncounter();
    }

    /**
     * Map property effective
     */
    protected function mapEffective(): void
    {
        $this->effective = $this->object_mapping->mapEffective();
    }

    /**
     * Map property issued
     */
    protected function mapIssued(): void
    {
        $this->issued = $this->object_mapping->mapIssued();
    }

    /**
     * Map property performer
     * @throws Exception
     */
    protected function mapPerformer(): void
    {
        $this->performer = $this->object_mapping->mapPerformer();
    }

    /**
     * Map property value
     */
    protected function mapValue(): void
    {
        $this->value = $this->object_mapping->mapValue();
    }

    /**
     * Map property dataAbsentReason
     */
    protected function mapDataAbsentReason(): void
    {
        $this->dataAbsentReason = $this->object_mapping->mapDataAbsentReason();
    }

    /**
     * Map property interpretation
     */
    protected function mapInterpretation(): void
    {
        $this->interpretation = $this->object_mapping->mapInterpretation();
    }

    /**
     * Map property note
     */
    protected function mapNote(): void
    {
        $this->note = $this->object_mapping->mapNote();
    }

    /**
     * Map property bodySite
     */
    protected function mapBodySite(): void
    {
        $this->bodySite = $this->object_mapping->mapBodySite();
    }

    /**
     * Map property method
     */
    protected function mapMethod(): void
    {
        $this->method = $this->object_mapping->mapMethod();
    }

    /**
     * Map property specimen
     */
    protected function mapSpecimen(): void
    {
        $this->specimen = $this->object_mapping->mapSpecimen();
    }

    /**
     * Map property device
     */
    protected function mapDevice(): void
    {
        $this->device = $this->object_mapping->mapDevice();
    }

    /**
     * Map property referenceRange
     */
    protected function mapReferenceRange(): void
    {
        $this->referenceRange = $this->object_mapping->mapReferenceRange();
    }

    /**
     * Map property hasMember
     */
    protected function mapHasMember(): void
    {
        $this->hasMember = $this->object_mapping->mapHasMember();
    }

    /**
     * Map property derivedFrom
     */
    protected function mapDerivedFrom(): void
    {
        $this->derivedFrom = $this->object_mapping->mapDerivedFrom();
    }

    /**
     * Map property component
     */
    protected function mapComponent(): void
    {
        $this->component = $this->object_mapping->mapComponent();
    }
}
