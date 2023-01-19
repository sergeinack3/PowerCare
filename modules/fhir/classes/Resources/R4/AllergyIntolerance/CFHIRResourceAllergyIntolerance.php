<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\AllergyIntolerance;

use Ox\Interop\Fhir\Contracts\Mapping\R4\AllergyIntoleranceMappingInterface;
use Ox\Interop\Fhir\Contracts\Resources\ResourceAllergieIntoleranceInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataType;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\AllergyIntolerance\CFHIRDataTypeAllergyIntoleranceReaction;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionCreate;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionDelete;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionRead;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionSearch;
use Ox\Interop\Fhir\Interactions\CFHIRInteractionUpdate;
use Ox\Interop\Fhir\Resources\CFHIRDomainResource;
use Ox\Interop\Fhir\Utilities\CCapabilitiesResource;

/**
 * Description
 *
 * @see http://hl7.org/fhir/allergyintolerance.html
 */
class CFHIRResourceAllergyIntolerance extends CFHIRDomainResource implements ResourceAllergieIntoleranceInterface
{
    /** @var string */
    public const CRITICALITY_LOW = 'low';
    /** @var string */
    public const CRITICALITY_HIGH = 'high';

    // constants
    /** @var string */
    public const RESOURCE_TYPE = 'AllergyIntolerance';

    // attributes
    protected ?CFHIRDataTypeCodeableConcept $clinicalStatus = null;

    protected ?CFHIRDataTypeCodeableConcept $verificationStatus = null;

    protected ?CFHIRDataTypeCode $type = null;

    /** @var CFHIRDataTypeCode[] */
    protected array $category = [];

    protected ?CFHIRDataTypeCode $criticality = null;

    protected ?CFHIRDataTypeCodeableConcept $code = null;

    protected ?CFHIRDataTypeReference $patient = null;

    protected ?CFHIRDataTypeReference $encounter = null;

    /** @var CFHIRDataType|CFHIRDataTypeChoice|null */
    protected ?CFHIRDataType $onset = null;

    protected ?CFHIRDataTypeDateTime $recordedDate = null;

    protected ?CFHIRDataTypeReference $recorder = null;

    /** @var CFHIRDataTypeReference|null */
    protected ?CFHIRDataTypeReference $asserter = null;

    /** @var CFHIRDataTypeDateTime|null */
    protected ?CFHIRDataTypeDateTime $lastOccurrence = null;

    /** @var CFHIRDataTypeAnnotation[] */
    protected array $note = [];

    /** @var CFHIRDataTypeAllergyIntoleranceReaction[] */
    protected array $reaction = [];

    /** @var AllergyIntoleranceMappingInterface */
    protected $object_mapping;

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
     * @param CFHIRDataTypeCodeableConcept|null $clinicalStatus
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setClinicalStatus(?CFHIRDataTypeCodeableConcept $clinicalStatus): CFHIRResourceAllergyIntolerance
    {
        $this->clinicalStatus = $clinicalStatus;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getClinicalStatus(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->clinicalStatus;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $verificationStatus
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setVerificationStatus(
        ?CFHIRDataTypeCodeableConcept $verificationStatus
    ): CFHIRResourceAllergyIntolerance {
        $this->verificationStatus = $verificationStatus;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCodeableConcept|null
     */
    public function getVerificationStatus(): ?CFHIRDataTypeCodeableConcept
    {
        return $this->verificationStatus;
    }

    /**
     * @param CFHIRDataTypeCode|null $type
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setType(?CFHIRDataTypeCode $type): CFHIRResourceAllergyIntolerance
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getType(): ?CFHIRDataTypeCode
    {
        return $this->type;
    }

    /**
     * @param CFHIRDataTypeCode ...$category
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setCategory(CFHIRDataTypeCode ...$category): CFHIRResourceAllergyIntolerance
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @param CFHIRDataTypeCode ...$category
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function addCategory(CFHIRDataTypeCode ...$category): CFHIRResourceAllergyIntolerance
    {
        $this->category = array_merge($this->category ?? [], $category);

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode[]
     */
    public function getCategory(): array
    {
        return $this->category ?: [];
    }

    /**
     * @param CFHIRDataTypeCode|null $criticality
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setCriticality(?CFHIRDataTypeCode $criticality): CFHIRResourceAllergyIntolerance
    {
        $this->criticality = $criticality;

        return $this;
    }

    /**
     * @return CFHIRDataTypeCode|null
     */
    public function getCriticality(): ?CFHIRDataTypeCode
    {
        return $this->criticality;
    }

    /**
     * @param CFHIRDataTypeCodeableConcept|null $code
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setCode(?CFHIRDataTypeCodeableConcept $code): CFHIRResourceAllergyIntolerance
    {
        $this->code = $code;

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
     * @param CFHIRDataTypeReference|null $patient
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setPatient(?CFHIRDataTypeReference $patient): CFHIRResourceAllergyIntolerance
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getPatient(): ?CFHIRDataTypeReference
    {
        return $this->patient;
    }

    /**
     * @param CFHIRDataTypeReference|null $encounter
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setEncounter(?CFHIRDataTypeReference $encounter): CFHIRResourceAllergyIntolerance
    {
        $this->encounter = $encounter;

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
     * @param CFHIRDataTypeChoice|CFHIRDataType|null $onset
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setOnset(?CFHIRDataType $onset): CFHIRResourceAllergyIntolerance
    {
        $this->onset = $onset;

        return $this;
    }

    /**
     * @return CFHIRDataTypeChoice|CFHIRDataType|null
     */
    public function getOnset(): ?CFHIRDataType
    {
        return $this->onset;
    }

    /**
     * @param CFHIRDataTypeDateTime|null $recordedDate
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setRecordedDate(?CFHIRDataTypeDateTime $recordedDate): CFHIRResourceAllergyIntolerance
    {
        $this->recordedDate = $recordedDate;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getRecordedDate(): ?CFHIRDataTypeDateTime
    {
        return $this->recordedDate;
    }

    /**
     * @param CFHIRDataTypeReference|null $recorder
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setRecorder(?CFHIRDataTypeReference $recorder): CFHIRResourceAllergyIntolerance
    {
        $this->recorder = $recorder;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getRecorder(): ?CFHIRDataTypeReference
    {
        return $this->recorder;
    }

    /**
     * @param CFHIRDataTypeReference|null $asserter
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setAsserter(?CFHIRDataTypeReference $asserter): CFHIRResourceAllergyIntolerance
    {
        $this->asserter = $asserter;

        return $this;
    }

    /**
     * @return CFHIRDataTypeReference|null
     */
    public function getAsserter(): ?CFHIRDataTypeReference
    {
        return $this->asserter;
    }

    /**
     * @param CFHIRDataTypeDateTime|null $lastOccurrence
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setLastOccurrence(?CFHIRDataTypeDateTime $lastOccurrence): CFHIRResourceAllergyIntolerance
    {
        $this->lastOccurrence = $lastOccurrence;

        return $this;
    }

    /**
     * @return CFHIRDataTypeDateTime|null
     */
    public function getLastOccurrence(): ?CFHIRDataTypeDateTime
    {
        return $this->lastOccurrence;
    }

    /**
     * @param CFHIRDataTypeAnnotation[] $note
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setNote(CFHIRDataTypeAnnotation ...$note): CFHIRResourceAllergyIntolerance
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAnnotation[] $note
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function addNote(CFHIRDataTypeAnnotation ...$note): CFHIRResourceAllergyIntolerance
    {
        $this->note = array_merge($this->note ?? [], $note);

        return $this;
    }

    /**
     * @return CFHIRDataTypeAnnotation[]
     */
    public function getNote(): array
    {
        return $this->note ?: [];
    }

    /**
     * @param CFHIRDataTypeAllergyIntoleranceReaction[] $reaction
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function setReaction(CFHIRDataTypeAllergyIntoleranceReaction ...$reaction): CFHIRResourceAllergyIntolerance
    {
        $this->reaction = $reaction;

        return $this;
    }

    /**
     * @param CFHIRDataTypeAllergyIntoleranceReaction[] $reaction
     *
     * @return CFHIRResourceAllergyIntolerance
     */
    public function addReaction(CFHIRDataTypeAllergyIntoleranceReaction ...$reaction): CFHIRResourceAllergyIntolerance
    {
        $this->reaction = array_merge($this->reaction ?? [], $reaction);

        return $this;
    }

    /**
     * @return CFHIRDataTypeAllergyIntoleranceReaction[]
     */
    public function getReaction(): array
    {
        return $this->reaction ?: [];
    }


    /**
     * Map property clinicalStatus
     */
    protected function mapClinicalStatus(): void
    {
        $this->clinicalStatus = $this->object_mapping->mapClinicalStatus();
    }

    /**
     * Map property verificationStatus
     */
    protected function mapVerificationStatus(): void
    {
        // only confirmed allergy is added
        $this->verificationStatus = $this->object_mapping->mapVerificationStatus();
    }

    /**
     * Map property type (only 'allergy' is supported)
     */
    protected function mapType(): void
    {
        $this->type = $this->object_mapping->mapType();
    }

    /**
     * Map property
     */
    protected function mapCategory(): void
    {
        $this->category = $this->object_mapping->mapCategory();
    }

    /**
     * Map property criticality
     */
    protected function mapCriticality(): void
    {
        $this->criticality = $this->object_mapping->mapCriticality();
    }

    /**
     * Map property code
     */
    protected function mapCode(): void
    {
        $this->code = $this->object_mapping->mapCode();
    }

    /**
     * Map property patient
     */
    protected function mapPatient(): void
    {
        $this->patient = $this->object_mapping->mapPatient();
    }

    /**
     * Map property encounter
     */
    protected function mapEncounter(): void
    {
        $this->encounter = $this->object_mapping->mapEncounter();
    }

    /**
     * Map property onset
     */
    protected function mapOnset(): void
    {
        $this->onset = $this->object_mapping->mapOnset();
    }

    /**
     * Map property recordedDate
     */
    protected function mapRecordedDate(): void
    {
        $this->recordedDate = $this->object_mapping->mapRecordedDate();
    }

    /**
     * Map property recorder
     */
    protected function mapRecorder(): void
    {
        $this->recorder = $this->object_mapping->mapRecorder();
    }

    /**
     * Map property asserter
     */
    protected function mapAsserter(): void
    {
        $this->asserter = $this->object_mapping->mapAsserter();
    }

    /**
     * Map property lastOccurrence
     */
    protected function mapLastOccurrence(): void
    {
        $this->lastOccurrence = $this->object_mapping->mapLastOccurrence();
    }

    /**
     * Map property note
     */
    protected function mapNote(): void
    {
        $this->note = $this->object_mapping->mapNote();
    }

    /**
     * Map property reaction
     */
    protected function mapReaction(): void
    {
        $this->reaction = $this->object_mapping->mapReaction();
    }
}
