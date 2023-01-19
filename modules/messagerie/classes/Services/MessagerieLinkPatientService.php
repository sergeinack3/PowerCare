<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Messagerie\Services;

use Exception;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Patients\CDossierMedical;
use Ox\Mediboard\Patients\CEvenementPatient;
use Ox\Mediboard\PlanningOp\CSejour;

class MessagerieLinkPatientService
{
    /** @var int $patient_id Patient identifier associated with the service. */
    private int $patient_id;

    /** @var int|null $group_id Current group identifier. */
    private ?int $group_id;

    /** @var string|null $offset Limit offset of search. */
    private ?string $offset;

    /** @var bool $allow_count Count result. */
    private bool $allow_count;

    /**
     * Constructor
     *
     * @param int         $patient_id   Patient identifier.
     * @param int|null    $group_id     Group identifier.
     * @param string|null $offset       Offset search.
     * @param bool        $allow_count  Allow to count result (default => true).
     */
    public function __construct(
        int $patient_id,
        ?int $group_id,
        ?string $offset,
        bool $allow_count = true
    ) {
        $this->patient_id    = $patient_id;
        $this->group_id      = $group_id;
        $this->offset        = $offset;
        $this->allow_count   = $allow_count;
    }

    /**
     * Load the patient's hospitalization using parameters.
     *
     * @return array{
     *     result: CSejour[],
     *     count:  int
     * }
     * @throws Exception
     */
    public function loadPatientHospitalizations(): array
    {
        $hospitalization = new CSejour();
        $ds              = $hospitalization->getDS();

        $where = [
            'patient_id' => $ds->prepare('= ?', $this->patient_id),
            'annule'     => $ds->prepare('= ?', '0'),
        ];

        if ($this->group_id) {
            $where['group_id'] = $ds->prepare('= ?', $this->group_id);
        }

        $hospitalization_count  = ($this->allow_count) ? $hospitalization->countList($where) : null;
        $hospitalization_result = $hospitalization->loadList($where, 'entree DESC, sejour_id DESC', $this->offset);

        foreach ($hospitalization_result as $hospitalization) {
            $this->loadHospitalizationRefs($hospitalization);

            foreach ($hospitalization->_ref_consultations as $consultation) {
                $consultation->loadRefPlageConsult();
            }
        }

        return [
            'result' => $hospitalization_result,
            'count'  => $hospitalization_count
        ];
    }

    /**
     * Load the patient's consultations using parameters.
     * Exclude results including a hospitalizations.
     *
     * @return array{
     *     result: CConsultation[],
     *     count:  int
     * }
     * @throws Exception
     */
    public function loadPatientConsultations(): array
    {
        $consultation = new CConsultation();
        $ds           = $consultation->getDS();

        $where = [
            'patient_id' => $ds->prepare('= ?', $this->patient_id),
            'sejour_id'  => $ds->prepare('IS NULL'),
            'annule'     => $ds->prepare('= ?', '0'),
        ];

        if ($this->group_id) {
            $where['group_id'] = $ds->prepare('= ?', $this->group_id);
        }

        $ljoin = [
            'plageconsult'        => 'plageconsult.plageconsult_id = consultation.plageconsult_id',
            'users_mediboard'     => 'users_mediboard.user_id = plageconsult.chir_id',
            'functions_mediboard' => 'functions_mediboard.function_id = users_mediboard.function_id',
        ];

        $consultation_count  = ($this->allow_count) ? $consultation->countList($where, null, $ljoin) : null;
        $consultation_result = $consultation->loadList(
            $where,
            'date DESC, heure DESC, consultation_id DESC',
            $this->offset,
            null,
            $ljoin
        );

        CStoredObject::massLoadFwdRef($consultation_result, "plageconsult_id");
        CStoredObject::massLoadFwdRef($consultation_result, "sejour_id");

        foreach ($consultation_result as $consultation) {
            $consultation->getType();
            $consultation->loadRefPlageConsult();
        }

        return [
            'result' => $consultation_result,
            'count'  => $consultation_count
        ];
    }

    /**
     * Load the patient's events using parameters.
     *
     * @return array{
     *     result: CEvenementPatient[],
     *     count:  int
     * }
     * @throws Exception
     */
    public function loadPatientEvents(): array
    {
        $event = new CEvenementPatient();
        $ds    = $event->getDS();

        $medical_folder               = new CDossierMedical();
        $medical_folder->object_class = 'CPatient';
        $medical_folder->object_id    = $this->patient_id;
        $medical_folder->loadMatchingObject();

        $where = [
            'dossier_medical_id' => $ds->prepare('= ?', $medical_folder->_id),
            'cancel'             => $ds->prepare('= ?', '0'),
        ];

        $event_count  = ($this->allow_count) ? $event->countList($where) : null;
        $event_result = $event->loadList($where, 'date DESC, evenement_patient_id DESC', $this->offset);

        return [
            'result' => $event_result,
            'count'  => $event_count
        ];
    }

    /**
     * Load the hospitalization references (Operations + Consultations).
     *
     * @param CSejour $hospitalization
     *
     * @return void
     * @throws Exception
     */
    private function loadHospitalizationRefs(CSejour $hospitalization): void
    {
        $ds = $hospitalization->getDS();

        $hospitalization->_ref_consultations = $hospitalization->loadBackRefs(
            'consultations',
            'date DESC, heure DESC, consultation_id DESC',
            null,
            null,
            ['plageconsult' => 'plageconsult.plageconsult_id = consultation.plageconsult_id'],
            null,
            null,
            ['annule' => $ds->prepare('= ?', '0')]
        );

        $hospitalization->_ref_operations = $hospitalization->loadBackRefs(
            'operations',
            'date DESC, operation_id DESC',
            null,
            null,
            null,
            null,
            null,
            ['annulee' => $ds->prepare('= ?', '0')]
        );
    }
}
