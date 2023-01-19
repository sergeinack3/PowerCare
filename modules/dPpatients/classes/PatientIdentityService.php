<?php

/**
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Patients;

use Exception;
use Ox\Core\Autoload\IShortNameAutoloadable;
use Ox\Core\CAppUI;
use Ox\Core\CMbException;
use Ox\Core\CMbString;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;

class PatientIdentityService implements IShortNameAutoloadable
{
    /** @var CSQLDataSource|null */
    public $ds;

    /** @var CMediusers */
    public $curr_user;

    public const PATIENT_ORDER = "nom ASC, prenom ASC";

    public const PATIENT_GROUP_BY = "patients.patient_id";

    public const PATIENT_GROUP_BY_DPOT = "patient_link.patient_id1";

    public const PAGE_LIMIT = 30;

    private int $limit = self::PAGE_LIMIT;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->ds        = CSQLDataSource::get("std");
        $this->curr_user = CMediusers::get();
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param string $date_min
     * @param string $date_max
     * @param string $state
     * @param int    $page
     *
     * @return array
     * @throws CMbException
     * @throws Exception
     */
    public function listPatientsFromState(
        string $state,
        ?string $date_min = null,
        ?string $date_max = null,
        int $page = 0
    ): array {
        $state = CMbString::upper($state);

        if (($date_max && $date_min) && $date_max < $date_min) {
            throw new CMbException(CAppUI::tr("common-error-Date min must be lower than date max"));
        }

        if ($page < 0) {
            throw new CMbException("page < 0");
        }

        if (!in_array($state, CPatientState::LIST_STATE)) {
            throw new CMbException("invalid status");
        }

        $patients_count = CPatientState::getAllNumberPatient($date_min, $date_max);
        $patients       = [];

        if ($patients_count[$state] > 0) {
            $where    = $this->prepareWhereFromConditions($state, $date_min, $date_max);
            $leftjoin = $this->prepareLeftJoinFromConditions($state, $date_min, $date_max);
            $patients = $this->loadPatients($state, $where, $leftjoin, $page);
        }

        return [$patients, $patients_count];
    }

    /**
     * @param string $state
     * @param array  $where
     * @param array  $leftjoin
     * @param int    $page
     *
     * @return array|null
     * @throws Exception
     */
    public function loadPatients(
        string $state,
        array $where,
        array $leftjoin,
        int $page
    ): ?array {
        if ($state === CPatientState::STATE_DPOT) {
            $patients = $this->loadDuplicatePatients($where, $leftjoin, $page);
        } else {
            unset($leftjoin['patients']);
            $patients = (new CPatient())->loadList(
                $where,
                self::PATIENT_ORDER,
                "$page, " . $this->getLimit(),
                self::PATIENT_GROUP_BY,
                $leftjoin
            );
        }

        CPatient::massLoadIPP($patients);
        /** @var CPatientState $patients_state */
        $patients_state = CPatient::massLoadBackRefs($patients, "patient_state", "datetime DESC");
        $mediusers      = CPatientState::massLoadFwdRef($patients_state, "mediuser_id");


        if ($patients_state) {
            foreach ($patients_state as $_patient_state) {
                /** @var CPatient $patient */
                $patient = $patients[$_patient_state->patient_id];

                $_patient_state->_ref_patient  = $patient;
                $_patient_state->_ref_mediuser = $mediusers[$_patient_state->mediuser_id];
            }
        }

        foreach ($patients as $_patient) {
            $_patient->_ref_last_patient_states = current($_patient->_back["patient_state"]);
            if ($state == CPatientState::STATE_DPOT) {
                $_patient->_ref_patient_links = array_merge(
                    $_patient->_back["patient_link1"],
                    $_patient->_back["patient_link2"]
                );
            }
        }

        return $patients;
    }

    /**
     * @param array $where
     * @param array $leftjoin
     * @param int   $page
     *
     * @return array
     * @throws Exception
     */
    private function loadDuplicatePatients(array $where, array $leftjoin, int $page = 0): array
    {
        $patient_link = new CPatientLink();
        $limit        = "$page, " . $this->getLimit();
        $patient_ids  = $patient_link->loadColumn(
            "patient_id1",
            $where,
            $leftjoin,
            $limit,
            true,
            true,
            self::PATIENT_GROUP_BY_DPOT,
        );

        $where_patient = ["patient_id" => CSQLDataSource::prepareIn($patient_ids)];

        $patients = (new CPatient())->loadList($where_patient, self::PATIENT_ORDER);

        CStoredObject::massLoadBackRefs($patients, "patient_link1");
        CStoredObject::massLoadBackRefs($patients, "patient_link2");

        foreach ($patients as $_patient) {
            /** @var CPatient $_patient */
            $_patient->loadPatientLinks();
        }

        return $patients;
    }

    /**
     * @param string $date_min
     * @param string $date_max
     * @param string $state
     *
     * @return array
     */
    protected function prepareWhereFromConditions(
        string $state,
        ?string $date_min = null,
        ?string $date_max = null
    ): array {
        $where = [];
        if ($date_min) {
            $where[] = $this->ds->prepare("entree >= ?", $date_min);
        }

        if ($date_max) {
            $where[] = $this->ds->prepare("entree <= ?", $date_max);
        }

        if (CAppUI::isCabinet()) {
            $where['patients.function_id'] = $this->ds->prepare('= ?', $this->curr_user->function_id);
        } elseif (CAppUI::isGroup()) {
            $where['patients.group_id'] = $this->ds->prepare('= ?', $this->curr_user->loadRefFunction()->group_id);
        } elseif ($date_min || $date_max) {
            $where['sejour.group_id'] = $this->ds->prepare('= ?', CGroups::loadCurrent()->_id);
        }

        if ($state !== CPatientState::STATE_DPOT && $state !== CPatientState::STATE_ANOM) {
            $where["status"] = $this->ds->prepare(" = ?", $state);
            if ($state !== CPatientState::STATE_VALI) {
                $where["vip"] = $this->ds->prepare(" = ?", 0);
            }

            if ($state === CPatientState::STATE_CACH) {
                $where["vip"]    = $this->ds->prepare(" = ?", 1);
                $where["status"] = $this->ds->prepare(" != ?", CPatientState::STATE_VALI);
            }
        } elseif ($state === CPatientState::STATE_ANOM) {
            $where['prenom'] = "= 'ANONYME' AND ( `patients`.`nom` = 'ANONYME' OR `nom` = `patients`.`patient_id`)";
        }

        return $where;
    }

    /**
     * @param string $date_min
     * @param string $date_max
     * @param string $state
     *
     * @return string[]
     */
    protected function prepareLeftJoinFromConditions(
        string $state,
        ?string $date_min = null,
        ?string $date_max = null
    ): array {
        $leftjoin = ['patients' => 'patients.patient_id = sejour.patient_id1'];

        if ($date_min || $date_max) {
            $leftjoin["sejour"] = "patients.patient_id = sejour.patient_id";
        }

        if ($state === CPatientState::STATE_DPOT) {
            $leftjoin = [
                "patients" => "patients.patient_id = patient_link.patient_id1",
                "sejour"   => "sejour.patient_id = patient_link.patient_id1",
            ];
        }

        return $leftjoin;
    }
}
