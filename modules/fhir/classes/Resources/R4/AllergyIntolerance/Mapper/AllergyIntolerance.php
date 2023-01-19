<?php

/**
 * @package Mediboard\fhir
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\AllergyIntolerance\Mapper;

use Exception;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\AllergyIntoleranceMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\AllergyIntolerance\CFHIRDataTypeAllergyIntoleranceReaction;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAge;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeAnnotation;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeChoice;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeReference;
use Ox\Interop\Fhir\Exception\CFHIRExceptionNotFound;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\AllergyIntolerance\CFHIRResourceAllergyIntolerance;
use Ox\Interop\Fhir\Resources\R4\Encounter\CFHIRResourceEncounter;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Mediboard\Cim10\CCodeCIM10;
use Ox\Mediboard\Patients\CAntecedent;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CDossierTiers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Mediboard\Ucum\Ucum;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 *
 * @see http://hl7.org/fhir/allergyintolerance.html
 */
class AllergyIntolerance implements DelegatedObjectMapperInterface, AllergyIntoleranceMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CAntecedent */
    protected $object;

    /** @var CFHIRResourceAllergyIntolerance */
    protected CFHIRResource $resource;

    protected CPatient $patient;

    /**
     * @inheritDoc
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
        return [CFHIRResourceAllergyIntolerance::class];
    }

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        if (!$object instanceof CAntecedent || $object->type !== 'alle') {
            return false;
        }

        return true;
    }

    /**
     * @param CFHIRResourceAllergyIntolerance $resource
     * @param CAntecedent|mixed               $object
     *
     * @return void
     * @throws Exception
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->resource = $resource;
        $this->object   = $object;
        $this->patient  = $this->getPatient();
    }

    /**
     * @return CPatient
     * @throws Exception|CFHIRExceptionNotFound
     */
    protected function getPatient(): CPatient
    {
        $patient = null;

        // Patient From Dossier Tiers
        if ($dossier_tiers = $this->getDossierTiers()) {
            $dossier_tiers->loadRefObject();
            if (($patient = $dossier_tiers->_ref_object) instanceof CSejour) {
                $patient = $patient->loadRefPatient();
            }
        }

        // Patient from Dossier Medical
        if ($dossier_medical = $this->getDossierMedical()) {
            $patient = $dossier_medical->loadRefObject();
            if (!$patient instanceof CPatient) {
                $patient = $patient->loadRefPatient();
            }
        }

        if (!$patient instanceof CPatient || !$patient->_id) {
            throw new CFHIRExceptionNotFound('Patient not found');
        }

        return $patient;
    }


    /**
     * @return CDossierMedical|null
     * @throws Exception
     */
    protected function getDossierMedical(): ?CDossierMedical
    {
        if (!$this->object->dossier_medical_id) {
            return null;
        }

        $dossier_medical = $this->object->_ref_dossier_medical ?: $this->object->loadRefDossierMedical();

        return $dossier_medical && $dossier_medical->_id ? $dossier_medical : null;
    }

    /**
     * @return CDossierTiers|null
     * @throws Exception
     */
    protected function getDossierTiers(): ?CDossierTiers
    {
        if (!$this->object->dossier_tiers_id) {
            return null;
        }

        $dossier_tiers = $this->object->_ref_dossier_tiers ?: $this->object->loadRefDossierTiers();

        return $dossier_tiers && $dossier_tiers->_id ? $dossier_tiers : null;
    }

    /**
     * @return CSejour|null
     * @throws Exception
     */
    protected function getSejour(): ?CSejour
    {
        $sejour = null;

        // Sejour From Dossier Tiers
        if ($dossier_tiers = $this->getDossierTiers()) {
            if ($dossier_tiers->object_class === 'CSejour') {
                $dossier_tiers->loadRefObject();
                $sejour = $dossier_tiers->_ref_object;
            }
        }

        // Sejour From Dossier Medical
        if ($dossier_medical = $this->getDossierMedical()) {
            $sejour = $dossier_medical->loadRefObject();
            if (!$sejour instanceof CSejour) {
                $sejour = null;
            }
        }

        return ($sejour && $sejour->_id) ? $sejour : null;
    }

    /**
     * @inheritDoc
     */
    public function mapClinicalStatus(): ?CFHIRDataTypeCodeableConcept
    {
        $code    = 'active';
        $display = 'Active';

        // is resolved
        if ($this->object->date_fin) {
            $code    = 'resolved';
            $display = 'Resolved';
        }

        // Absence d'allergie
        if ($this->object->absence) {
            $code    = 'inactive';
            $display = 'Inactive';
        }

        $coding = (new CFHIRDataTypeCoding())
            ->setCode($code)
            ->setDisplay($display)
            ->setSystem('http://terminology.hl7.org/CodeSystem/allergyintolerance-clinical');

        return (new CFHIRDataTypeCodeableConcept())
            ->setCoding($coding);
    }

    /**
     * @inheritDoc
     */
    public function mapVerificationStatus(): ?CFHIRDataTypeCodeableConcept
    {
        $coding = (new CFHIRDataTypeCoding())
            ->setSystem('http://terminology.hl7.org/CodeSystem/allergyintolerance-verification')
            ->setCode('confirmed')
            ->setDisplay('Confirmed');

        return (new CFHIRDataTypeCodeableConcept())
            ->setCoding($coding);
    }

    /**
     * @inheritDoc
     */
    public function mapType(): ?CFHIRDataTypeCode
    {
        return new CFHIRDataTypeCode('allergy');
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
    public function mapCriticality(): ?CFHIRDataTypeCode
    {
        $criticality = ($this->object->majeur || $this->object->important)
            ? CFHIRResourceAllergyIntolerance::CRITICALITY_HIGH
            : CFHIRResourceAllergyIntolerance::CRITICALITY_LOW;

        return new CFHIRDataTypeCode($criticality);
    }

    /**
     * @inheritDoc
     */
    public function mapCode(): ?CFHIRDataTypeCodeableConcept
    {
        $coding = [];
        // CIM 10
        if ($this->object->extractCim10Codes()) {
            /** @var CCodeCIM10 $cim_10_detail */
            foreach ($this->object->_codes_cim10 as $cim_10_code) {
                $cim_10_detail = $this->object->_codes_cim10_detail[$cim_10_code];

                $coding[] = (new CFHIRDataTypeCoding())
                    ->setSystem('http://hl7.org/fhir/sid/icd-10')
                    ->setCode($cim_10_code)
                    ->setDisplay($cim_10_detail->libelle);
            }
        }

        // Snomed
        if ($this->object->loadRefsCodesSnomed()) {
            foreach ($this->object->_ref_codes_snomed as $code_snomed) {
                $coding[] = (new CFHIRDataTypeCoding())
                    ->setSystem('http://snomed.info/sct')
                    ->setCode($code_snomed->code)
                    ->setDisplay($code_snomed->libelle);
            }
        }

        // Loinc
        if ($this->object->loadRefsCodesLoinc()) {
            foreach ($this->object->_ref_codes_loinc as $code_loinc) {
                $coding[] = (new CFHIRDataTypeCoding())
                    ->setSystem('http://loinc.org')
                    ->setCode($code_loinc->code)
                    ->setDisplay($code_loinc->libelle_fr);
            }
        }

        return (new CFHIRDataTypeCodeableConcept())
            ->setCoding(...$coding);
    }

    /**
     * @inheritDoc
     */
    public function mapPatient(): ?CFHIRDataTypeReference
    {
        return $this->resource->addReference(CFHIRResourcePatient::class, $this->patient);
    }


    /**
     * @return CFHIRDataTypeReference|null
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws Exception
     */
    public function mapEncounter(): ?CFHIRDataTypeReference
    {
        return ($sejour = $this->getSejour())
            ? $this->resource->addReference(CFHIRResourceEncounter::class, $sejour)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function mapOnset(): ?CFHIRDataTypeChoice
    {
        if (!$this->object->date) {
            return null;
        }

        $start_year = substr($this->object->date, 0, -6);
        if ($is_period = $this->object->date && $this->object->date_fin) {
            $end_year = substr($this->object->date_fin, 0, -6);

            $start_datetime = $start_year . str_replace('00', '01', substr($this->object->date, 4));
            $end_datetime   = $end_year . str_replace('00', '01', substr($this->object->date_fin, 4));

            $data = [
                'start' => $start_datetime,
                'end'   => $end_datetime,
            ];

            return new CFHIRDataTypeChoice(CFHIRDataTypePeriod::class, $data);
        }

        // Only Year ==> use OnsetAge (UCUM system)
        if ($is_ucum = preg_match('#^\d{4}-00-00$#', $this->object->date)) {
            $data = [
                'value'  => $start_year,
                'system' => Ucum::CODE_SYSTEM,
                'code'   => 'a',
                'unit'   => 'year',
            ];

            return new CFHIRDataTypeChoice(CFHIRDataTypeAge::class, $data);
        }

        return new CFHIRDataTypeChoice(CFHIRDataTypeDateTime::class, $this->object->date);
    }

    /**
     * @inheritDoc
     */
    public function mapRecordedDate(): ?CFHIRDataTypeDateTime
    {
        return new CFHIRDataTypeDateTime($this->object->creation_date);
    }

    /**
     * @return CFHIRDataTypeReference|null
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function mapRecorder(): ?CFHIRDataTypeReference
    {
        $reference = null;

        // Reference Patient From AppFine
        if ($dossier_tiers = $this->getDossierTiers()) {
            if ($dossier_tiers->name === CDossierTiers::NAME_APPFINE) {
                $reference = $this->resource->addReference(CFHIRResourcePatient::class, $this->patient);
            }
        }

        // Reference Mediusers
        if (!$reference) {
            $mediusers = $this->object->loadRefOwner();
            if ($mediusers && $mediusers->_id) {
                $reference = $this->resource->addReference(CFHIRResourcePractitioner::class, $mediusers);
            }
        }

        return $reference;
    }

    /**
     * @inheritDoc
     * @return CFHIRDataTypeReference|null
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function mapAsserter(): ?CFHIRDataTypeReference
    {
        $reference = null;
        // Reference Patient From AppFine
        if ($dossier_tiers = $this->getDossierTiers()) {
            if ($dossier_tiers->name === CDossierTiers::NAME_APPFINE) {
                $reference = $this->resource->addReference(CFHIRResourcePatient::class, $this->patient);
            }
        }

        return $reference;
    }

    /**
     * @inheritDoc
     */
    public function mapLastOccurrence(): ?CFHIRDataTypeDateTime
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function mapNote(): array
    {
        $notes   = [];
        $comment = ($this->object->comment ?: $this->object->rques);
        if ($comment) {
            $notes[] = (new CFHIRDataTypeAnnotation())
                ->setText($comment);
        }

        return $notes;
    }

    /**
     * @inheritDoc
     */
    public function mapReaction(): ?CFHIRDataTypeAllergyIntoleranceReaction
    {
        return null;
    }
}
