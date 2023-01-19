<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Appointment\Mapper;

use Exception;
use Ox\Core\CAppUI;
use Ox\Interop\Fhir\Contracts\Delegated\DelegatedObjectMapperInterface;
use Ox\Interop\Fhir\Contracts\Mapping\R4\AppointmentMappingInterface;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypePositiveInt;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeUnsignedInt;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Appointment\CFHIRDataTypeAppointmentParticipant;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypePeriod;
use Ox\Interop\Fhir\Profiles\CFHIR;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\CStoredObjectResourceDomainTrait;
use Ox\Interop\Fhir\Resources\R4\Appointment\CFHIRResourceAppointment;
use Ox\Interop\Fhir\Resources\R4\Patient\CFHIRResourcePatient;
use Ox\Interop\Fhir\Resources\R4\Practitioner\CFHIRResourcePractitioner;
use Ox\Interop\Fhir\Resources\R4\PractitionerRole\CFHIRResourcePractitionerRole;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CMedecin;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionException;

/**
 * Description
 */
class Appointment implements DelegatedObjectMapperInterface, AppointmentMappingInterface
{
    use CStoredObjectResourceDomainTrait;

    /** @var CConsultation */
    protected $object;

    /** @var CFHIRResourceAppointment */
    protected CFHIRResource $resource;

    /**
     * @param CFHIRResource $resource
     * @param mixed         $object
     *
     * @return bool
     */
    public function isSupported(CFHIRResource $resource, $object): bool
    {
        return $object instanceof CConsultation && $object->_id;
    }

    /**
     * @param CFHIRResource $resource
     * @param CConsultation|mixed $object
     *
     * @return void
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        $this->object   = $object;
        $this->resource = $resource;
        $this->object->loadRefPlageConsult();
    }

    /**
     * @return string[]
     */
    public function onlyProfiles(): ?array
    {
        return [CFHIR::class];
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceAppointment::class];
    }

    public function mapStatus(): ?CFHIRDataTypeCode
    {
        if ($this->object->annule) {
            if ($this->object->motif_annulation && $this->object->motif_annulation == 'not_arrived') {
                return new CFHIRDataTypeCode('noshow');
            } else {
                return new CFHIRDataTypeCode('cancelled');
            }
        }

        switch ($this->object->chrono) {
            case CConsultation::DEMANDE:
                return new CFHIRDataTypeCode('proposed');
            case CConsultation::PLANIFIE:
                return new CFHIRDataTypeCode('booked');
            case CConsultation::PATIENT_ARRIVE:
            case CConsultation::EN_COURS:
                return new CFHIRDataTypeCode('arrived');
            case CConsultation::TERMINE:
                return new CFHIRDataTypeCode('fulfilled');
            default:
        }

        return null;
    }

    public function mapCancelationReason(): ?CFHIRDataTypeCodeableConcept
    {
        if (!$this->object->annule || !$this->object->motif_annulation) {
            return null;
        }

        $system = 'urn:oid:2.16.840.1.113883.4.642.3.1381';

        if ($this->object->motif_annulation === 'not_arrived') {
            $code    = '';
            $display = '';
        } elseif ($this->object->motif_annulation === 'by_patient') {
            $code    = 'pat';
            $display = 'Patient';
        } else {
            $code    = 'other';
            $display = 'Other';
        }

        $text   = $display;
        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display);

        return CFHIRDataTypeCodeableConcept::addCodeable($coding, $text);
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

    /**
     * @throws Exception
     */
    public function mapServiceType(): array
    {
        if (!$this->object->categorie_id) {
            return [];
        }

        $categorie = $this->object->loadRefCategorie();

        return CFHIRDataTypeCodeableConcept::addCodeable(
            CFHIRDataTypeCoding::addCoding(
                'urn:oid:' . CAppUI::conf('mb_oid'),
                $categorie->_id,
                $categorie->nom_categorie
            ),
            null,
            []
        );
    }

    public function mapSpecialty(): array
    {
        // not implemented
        return [];
    }

    public function mapAppointmentType(): ?CFHIRDataTypeCodeableConcept
    {
        // not implemented
        return null;
    }

    public function mapReasonCode(): array
    {
        // not implemented
        return [];
    }

    public function mapReasonReference(): array
    {
        // not implemented
        return [];
    }

    public function mapPriority(): ?CFHIRDataTypeUnsignedInt
    {
        // not implemented
        return null;
    }

    public function mapDescription(): ?CFHIRDataTypeString
    {
        return new CFHIRDataTypeString($this->object->motif);
    }

    public function mapSupportingInformation(): array
    {
        // not implemented
        return [];
    }

    public function mapStart(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->_datetime);
    }

    public function mapEnd(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->_date_fin);
    }

    public function mapMinutesDuration(): ?CFHIRDataTypePositiveInt
    {
        return new CFHIRDataTypePositiveInt($this->object->_duree);
    }

    /**
     * @param string $resource_class
     *
     * @return array
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws Exception
     */
    public function mapSlot(string $resource_class): array
    {
        $slots = [];

        $refs_slots = $this->object->loadRefSlots();

        foreach ($refs_slots as $_slot) {
            $slots[] = $this->resource->addReference($resource_class, $_slot);
        }

        return $slots;
    }

    public function mapCreated(): ?CFHIRDataTypeDateTime
    {
        return new CFHIRDataTypeDateTime($this->object->_date);
    }

    public function mapComment(): ?CFHIRDataTypeString
    {
        return new CFHIRDataTypeString($this->object->rques);
    }

    public function mapPatientInstruction(): ?CFHIRDataTypeString
    {
        // not implemented
        return null;
    }

    public function mapBasedOn(): array
    {
        // not implemented
        return [];
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function mapParticipant(): array
    {
        $participants = [];

        $status_patient = $this->object->annule &&
        ($this->object->motif_annulation == 'by_patient' || $this->object->motif_annulation == 'not_arrived')
            ? new CFHIRDataTypeCode('declined') : new CFHIRDataTypeCode('accepted');

        $status_practitioner = $this->object->annule && $this->object->motif_annulation == 'other'
            ? new CFHIRDataTypeCode('declined') : new CFHIRDataTypeCode('accepted');

        $practitionerRole = $this->object->loadRefPraticien();

        if ($practitionerRole->_id) {
            // PractitionerRole
            $participant_role_praticien           = new CFHIRDataTypeAppointmentParticipant();
            $participant_role_praticien->actor    = $this->resource->addReference(
                CFHIRResourcePractitionerRole::class,
                $practitionerRole
            );
            $participant_role_praticien->required = new CFHIRDataTypeCode('required');
            $participant_role_praticien->status   = new CFHIRDataTypeCode('needs-action');

            $participants[] = $participant_role_praticien;
        }

        $practitioner       = new CMedecin();
        $practitioner->rpps = $practitionerRole->rpps;
        $practitioner->loadMatchingObject();

        if ($practitioner->_id) {
            // Practitioner
            $participant_praticien           = new CFHIRDataTypeAppointmentParticipant();
            $participant_praticien->type     = CFHIRDataTypeCodeableConcept::addCodeable(
                CFHIRDataTypeCoding::addCoding('urn:oid:2.16.840.1.113883.4.642.3.250', 'ADM', 'admitter'),
                'Praticien',
                []
            );
            $participant_praticien->actor    = $this->resource->addReference(
                CFHIRResourcePractitioner::class,
                $practitioner
            );
            $participant_praticien->required = new CFHIRDataTypeCode('required');
            $participant_praticien->status   = $status_practitioner;

            $participants[] = $participant_praticien;
        }

        $patient = $this->object->loadRefPatient();

        if ($patient->_id) {
            // Patient
            $participant_patient           = new CFHIRDataTypeAppointmentParticipant();
            $participant_patient->actor    = $this->resource->addReference(CFHIRResourcePatient::class, $patient);
            $participant_patient->required = new CFHIRDataTypeCode('required');
            $participant_patient->status   = $status_patient;

            $participants[] = $participant_patient;
        }

        return $participants;
    }

    /**
     * @throws ReflectionException
     * @throws InvalidArgumentException
     * @throws Exception
     */
    public function mapRequestedPeriod(): array
    {
        return [
            CFHIRDataTypePeriod::build(
                CFHIRDataTypeInstant::formatPeriod(
                    CFHIR::getTimeUtc($this->object->_datetime, false),
                    CFHIR::getTimeUtc($this->object->_date_fin, false)
                )
            ),
        ];
    }
}
