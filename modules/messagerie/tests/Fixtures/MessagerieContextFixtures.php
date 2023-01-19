<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CModelObjectException;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Bloc\CBlocOperatoire;
use Ox\Mediboard\Bloc\CPlageOp;
use Ox\Mediboard\Bloc\CSalle;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CPlageconsult;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\PlanningOp\COperation;
use Ox\Mediboard\PlanningOp\CSejour;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;
use Ox\Tests\Fixtures\GroupFixturesInterface;

/**
 * @description Data's used for messaging
 */
class MessagerieContextFixtures extends Fixtures implements GroupFixturesInterface
{
    public const MESSAGING_USER_TAG                         = 'messaging_user';
    public const MESSAGING_PATIENT_TAG                      = 'messaging_context_patient';
    public const MESSAGING_HOSPITALIZATION_TAG              = 'messaging_context_hospitalization';
    public const MESSAGING_HOSPITALIZATION_CONSULTATION_TAG = 'messaging_context_hospitalization_consultation';
    public const MESSAGING_OPERATION_TAG                    = 'messaging_context_operation';
    public const MESSAGING_CONSULTATION_TAG                 = 'messaging_context_consultation';
    public const MESSAGING_EVENT_TAG                        = 'messaging_context_event';

    /** @var CPatient $patient Patient used for messaging test. */
    private CPatient $patient;

    /** @var CMediusers $user User used for messaging test. */
    private CMediusers $user;

    /**
     * @inheritDoc
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        $this->createUser();
        $this->createPatient();
        $this->createEvent();

        $consultation_range = $this->createConsultationRange();
        $this->createConsultation($consultation_range->_id);

        $hospitalization = $this->createHospitalization();
        $this->createHospitalConsultation($consultation_range->_id, $hospitalization->_id);
        $this->createOperation($hospitalization->_id);
    }

    /**
     * @inheritDoc
     */
    public static function getGroup(): array
    {
        return ['messaging_fixtures', 100];
    }

    /**
     * Create user.
     *
     * @return void
     * @throws FixturesException
     */
    private function createUser(): void
    {
        $user              = $this->getUser(false);
        $user->_user_type  = 13;
        $user->actif       = 1;

        $this->store($user, self::MESSAGING_USER_TAG);

        $user->loadRefFunction();
        $this->user = $user;
    }

    /**
     * Create patient.
     *
     * @return void
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createPatient(): void
    {
        /** @var CPatient $patient */
        $patient = CStoredObject::getSampleObject(CPatient::class);

        $this->store($patient, self::MESSAGING_PATIENT_TAG);
        $this->patient = $patient;
    }

    /**
     * Create an hospitalization.
     *
     * @return CSejour
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createHospitalization(): CSejour
    {
        /** @var CSejour $hospitalization */
        $hospitalization                = CStoredObject::getSampleObject(CSejour::class);
        $hospitalization->patient_id    = $this->patient->_id;
        $hospitalization->praticien_id  = $this->user->_id;
        $hospitalization->group_id      = $this->user->_ref_function->group_id;
        $hospitalization->entree        = CMbDT::dateTime();
        $hospitalization->entree_prevue = CMbDT::dateTime();
        $hospitalization->sortie        = CMbDT::dateTime("+2 days 12:00:00");
        $hospitalization->sortie_prevue = CMbDT::dateTime("+2 days 12:00:00");
        $hospitalization->annule        = 0;

        $this->store($hospitalization, self::MESSAGING_HOSPITALIZATION_TAG);
        return $hospitalization;
    }

    /**
     * Create an operation + prerequisites.
     *
     * @param int $hospitalization_id
     *
     * @return void
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createOperation(int $hospitalization_id): void
    {
        /** @var CBlocOperatoire $operating_room */
        $operating_room           = CStoredObject::getSampleObject(CBlocOperatoire::class);
        $operating_room->group_id = $this->user->_ref_function->group_id;
        $operating_room->nom      = 'Fixtures';
        $this->store($operating_room);

        /** @var CSalle $room */
        $room          = CStoredObject::getSampleObject(CSalle::class);
        $room->bloc_id = $operating_room->_id;
        $room->nom     = "Fixtures";
        $this->store($room);

        /** @var CPlageOp $operation_range */
        $operation_range                  = CStoredObject::getSampleObject(CPlageOp::class);
        $operation_range->chir_id         = $this->user->_id;
        $operation_range->salle_id        = $room->_id;
        $operation_range->date            = CMbDT::date();
        $operation_range->debut           = CMbDT::time("09:00:00");
        $operation_range->debut_reference = $operation_range->debut;
        $operation_range->fin             = CMbDT::time("21:00:00");
        $operation_range->fin_reference   = $operation_range->fin;
        $this->store($operation_range);

        /** @var COperation $operation */
        $operation            = CStoredObject::getSampleObject(COperation::class);
        $operation->chir_id   = $this->user->_id;
        $operation->sejour_id = $hospitalization_id;
        $this->store($operation, self::MESSAGING_OPERATION_TAG);
    }

    /**
     * Create consultation range
     *
     * @return CPlageconsult
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createConsultationRange(): CPlageconsult
    {
        /** @var CPlageconsult $consultation_range */
        $consultation_range          = CStoredObject::getSampleObject(CPlageconsult::class);
        $consultation_range->chir_id = $this->user->_id;
        $consultation_range->libelle = 'Fixtures';
        $consultation_range->date    = CMbDT::date();
        $consultation_range->debut   = CMbDT::time("09:00:00");
        $consultation_range->fin     = CMbDT::time("10:00:00");
        $consultation_range->freq    = CMbDT::time("00:15:00");
        $this->store($consultation_range);

        return $consultation_range;
    }

    /**
     * Create an hospital consultation + prerequisites.
     *
     * @param int $consultation_range_id Consultation range identifier
     * @param int $hospitalization_id
     *
     * @return void
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createHospitalConsultation(int $consultation_range_id, int $hospitalization_id): void
    {
        /** @var CConsultation $hospital_consultation */
        $hospital_consultation                  = CStoredObject::getSampleObject(CConsultation::class);
        $hospital_consultation->plageconsult_id = $consultation_range_id;
        $hospital_consultation->patient_id      = $this->patient->_id;
        $hospital_consultation->owner_id        = $this->user->_id;
        $hospital_consultation->sejour_id       = $hospitalization_id;
        $hospital_consultation->heure           = CMbDT::time("09:00:00");
        $hospital_consultation->annule          = 0;
        $this->store($hospital_consultation, self::MESSAGING_HOSPITALIZATION_CONSULTATION_TAG);
    }

    /**
     * Create an consultation + prerequisites.
     *
     * @param int    $consultation_range_id Consultation range identifier
     *
     * @return void
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createConsultation(int $consultation_range_id): void
    {
        /** @var CConsultation $consultation */
        $consultation                  = CStoredObject::getSampleObject(CConsultation::class);
        $consultation->plageconsult_id = $consultation_range_id;
        $consultation->patient_id      = $this->patient->_id;
        $consultation->owner_id        = $this->user->_id;
        $consultation->heure           = CMbDT::time("09:15:00");
        $consultation->annule          = 0;
        $this->store($consultation, self::MESSAGING_CONSULTATION_TAG);
    }

    /**
     * Create an event + prerequisites.
     *
     * @return void
     * @throws CModelObjectException
     * @throws FixturesException
     */
    private function createEvent(): void
    {
        /** @var CDossierMedical $medical_record */
        $medical_record = CStoredObject::getSampleObject(CDossierMedical::class);
        $medical_record->object_class = $this->patient->_class;
        $medical_record->object_id    = $this->patient->_id;
        $this->store($medical_record);

        /** @var CEvenementPatient $event */
        $event                     = CStoredObject::getSampleObject(CEvenementPatient::class);
        $event->praticien_id       = $this->user->_id;
        $event->dossier_medical_id = $medical_record->_id;
        $event->date               = CMbDT::dateTime();
        $event->libelle            = 'Fixtures';
        $event->description        = 'Fixtures';
        $event->cancel             = 0;
        $this->store($event, self::MESSAGING_EVENT_TAG);
    }
}
