<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Interop\Fhir\Resources\R4\Appointment\Mapper;

use Exception;
use Ox\AppFine\Server\CEvenementMedical;
use Ox\Core\CMbDT;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeCode;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeDateTime;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeInstant;
use Ox\Interop\Fhir\Datatypes\CFHIRDataTypeString;
use Ox\Interop\Fhir\Datatypes\Complex\Backbone\Appointment\CFHIRDataTypeAppointmentParticipant;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCodeableConcept;
use Ox\Interop\Fhir\Datatypes\Complex\CFHIRDataTypeCoding;
use Ox\Interop\Fhir\Profiles\CFHIRMES;
use Ox\Interop\Fhir\Resources\CFHIRResource;
use Ox\Interop\Fhir\Resources\R4\Appointment\Profiles\InteropSante\CFHIRResourceAppointmentFR;
use Ox\Interop\Fhir\Resources\R4\Patient\Profiles\InteropSante\CFHIRResourcePatientFR;
use Ox\Interop\Fhir\Resources\R4\Practitioner\Profiles\InteropSante\CFHIRResourcePractitionerFR;
use Ox\Mediboard\Patients\CMedecinExercicePlace;

/**
 * Description
 */
class ENSFrAppointment extends FrAppointment
{
    /** @var CEvenementMedical */
    protected $object;

    /** @var CFHIRResourceAppointmentFR */
    protected CFHIRResource $resource;

    /**
     * @throws Exception
     */
    public function setResource(CFHIRResource $resource, $object): void
    {
        parent::setResource($resource, $object);


        $this->object->loadRefResponsable();
        $this->object->loadRefExercicePlace();
        $this->object->loadRefPatient();
    }

    /**
     * @return string[]
     */
    public function onlyRessources(): array
    {
        return [CFHIRResourceAppointmentFR::class];
    }

    public function onlyProfiles(): array
    {
        return [CFHIRMES::class];
    }

    public function mapStatus(): ?CFHIRDataTypeCode
    {
        if ($this->object->cancel) {
            return new CFHIRDataTypeCode('cancelled');
        }

        if (CMbDT::dateTime() < $this->object->date_debut) {
            return new CFHIRDataTypeCode('booked');
        } elseif (CMbDT::dateTime() > $this->object->date_debut && CMbDT::dateTime() < $this->object->date_fin) {
            return new CFHIRDataTypeCode('arrived');
        } else {
            return new CFHIRDataTypeCode('fulfilled');
        }
    }

    public function mapServiceType(): array
    {
        $system       = 'http://terminology.hl7.org/ValueSet/v3-ActEncounterCode';
        $code         = $this->object->teleconsultation ? 'VR' : 'AMB';
        $display_name = $this->object->teleconsultation ? 'virtual' : 'ambulatory';

        $coding = CFHIRDataTypeCoding::addCoding($system, $code, $display_name);

        return CFHIRDataTypeCodeableConcept::addCodeable($coding);
    }

    /**
     * @throws Exception
     */
    public function mapSpecialty(): array
    {
        $practitioner = $this->object->_ref_responsable;
        $meps         = $practitioner->getMedecinExercicePlaces();
        $mep          = new CMedecinExercicePlace();

        /** @var CMedecinExercicePlace $_mep */
        foreach ($meps as $_mep) {
            if ($_mep->exercice_place_id === $this->object->_ref_exercice_place->_id) {
                $mep = $_mep;
            }
        }

        if ($mep->disciplines) {
            $coding = $this->setMedecinSpecialty($mep->disciplines);

            if (!empty($coding)) {
                return [CFHIRDataTypeCodeableConcept::addCodeable($coding)];
            }
        }

        return [];
    }


    /**
     * @param string $discipline
     *
     * @return array
     */
    private function setMedecinSpecialty(string $discipline): array
    {
        if (!$discipline) {
            return [];
        }

        $exploded_code = explode(' : ', $discipline);

        $system      = 'https://mos.esante.gouv.fr/NOS/TRE_R38-SpecialiteOrdinale/FHIR/TRE-R38-SpecialiteOrdinale';
        $code        = $exploded_code[0];
        $displayName = $exploded_code[1];

        return CFHIRDataTypeCoding::addCoding($system, $code, $displayName, []);
    }

    public function mapCreated(): ?CFHIRDataTypeDateTime
    {
        return new CFHIRDataTypeDateTime($this->object->creation_datetime);
    }

    public function mapStart(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->date_debut);
    }

    public function mapEnd(): ?CFHIRDataTypeInstant
    {
        return new CFHIRDataTypeInstant($this->object->date_fin);
    }

    public function mapComment(): ?CFHIRDataTypeString
    {
        return new CFHIRDataTypeString($this->object->remarques);
    }

    public function mapDescription(): ?CFHIRDataTypeString
    {
        return new CFHIRDataTypeString($this->object->libelle);
    }

    public function mapParticipant(): array
    {
        $participants = [];

        $patient = $this->object->_ref_patient;

        $participant_role_patient         = new CFHIRDataTypeAppointmentParticipant();
        $participant_role_patient->actor  = $this->resource->addReference(
            CFHIRResourcePatientFR::class,
            $patient
        );
        $participant_role_patient->status = new CFHIRDataTypeCode('accepted');

        $participants[] = $participant_role_patient;

        $practitioner = $this->object->_ref_responsable;

        $participant_role_practitioner         = new CFHIRDataTypeAppointmentParticipant();
        $participant_role_practitioner->actor  = $this->resource->addReference(
            CFHIRResourcePractitionerFR::class,
            $practitioner
        );
        $participant_role_practitioner->status = new CFHIRDataTypeCode('accepted');

        $participants[] = $participant_role_practitioner;

        return $participants;
    }
}
