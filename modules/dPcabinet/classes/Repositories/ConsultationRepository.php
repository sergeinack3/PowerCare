<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Repositories;

use DateTimeImmutable;
use Exception;
use Ox\Core\Api\Request\RequestRelations;
use Ox\Core\CPDODataSource;
use Ox\Core\CRequest;
use Ox\Core\CSQLDataSource;
use Ox\Core\CStoredObject;
use Ox\Core\Repositories\AbstractRequestApiRepository;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;

/**
 * Repository to fetch CSampleMovie objects.
 */
class ConsultationRepository extends AbstractRequestApiRepository
{
    /** @var CPDODataSource|CSQLDataSource */
    private $ds;

    public function __construct()
    {
        parent::__construct();
        $this->ds = $this->object->getDS();
    }

    /**
     * @throws Exception
     */
    public function countPremiere(array $users = [], ?DateTimeImmutable $date = null): int
    {
        $where = ["consultation.premiere" => $this->ds->prepare('= ?', '1')];

        return $this->count($users, $date, $where);
    }

    /**
     * @throws Exception
     */
    public function countCancelled(array $users = [], ?DateTimeImmutable $date = null): int
    {
        $where = ["consultation.annule" => $this->ds->prepare('= ?', '1')];

        return $this->count($users, $date, $where);
    }

    /**
     * @throws Exception
     */
    public function countFinished(array $users = [], ?DateTimeImmutable $date = null): int
    {
        $where = [
            "consultation.chrono" => $this->ds->prepare('= ?', '64'),
            "consultation.annule" => $this->ds->prepare('= ?', '0'),
        ];

        return $this->count($users, $date, $where);
    }

    /**
     * Count unpaid consultations
     * @Param CMediusers[] $users
     *
     * @throws Exception
     */
    public function countUnpaid(array $users = []): int
    {
        $ljoin = $this->joinFactureCabinet();
        $where = $this->addWhereDuPatientNotNull();

        /** @var CConsultation[] $consultations */
        $consultations = $this->find($users, null, $where, $ljoin);

        $consultations = $this->filterUnpaid($consultations);

        return count($consultations);
    }

    public function getFullyPaid(array $users = [], ?DateTimeImmutable $date = null): array
    {
        $ljoin = $this->joinReglement();
        $where = $this->addWhereDuPatientNotNull();
        $where = $this->addWhereCancelled(false, $where);
        $where = $this->addWhereSettlementNull($where);

        return $this->find($users, $date, $where, $ljoin);
    }

    public function countFullyPaid(array $users = [], ?DateTimeImmutable $date = null): int
    {
        $ljoin = $this->joinReglement();
        $where = $this->addWhereDuPatientNotNull();
        $where = $this->addWhereCancelled(false, $where);
        $where = $this->addWhereSettlementNull($where);

        return $this->count($users, $date, $where, $ljoin);
    }

    public function getPartiallyPaid(array $users = [], ?DateTimeImmutable $date = null): array
    {
        $consultation_ids = $this->getIdsWithMissingPayment(
            $users,
            $date,
            "consultation"
        );
        $where            = $this->addConsultationIdIn($consultation_ids);

        return $this->find($users, $date, $where);
    }

    public function countPartiallyPaid(array $users = [], ?DateTimeImmutable $date = null): int
    {
        $consultation_ids = $this->getIdsWithMissingPayment(
            $users,
            $date,
            "consultation"
        );
        $where            = $this->addConsultationIdIn($consultation_ids);

        return $this->count($users, $date, $where);
    }

    public function getPartiallyPaidBasedOnPatientHistory(array $users = [], ?DateTimeImmutable $date = null): array
    {
        $patients_id = $this->getIdsWithMissingPayment($users);
        $where       = $this->addPatientIdIn($patients_id);

        return $this->find($users, $date, $where);
    }

    public function countPartiallyPaidBasedOnPatientHistory(array $users = [], ?DateTimeImmutable $date = null): int
    {
        $patients_id = $this->getIdsWithMissingPayment($users);
        $where       = $this->addPatientIdIn($patients_id);

        return $this->count($users, $date, $where);
    }

    /**
     * @param array $consultations
     *
     * @return CConsultation[]
     */
    public function filterUnpaid(array $consultations): array
    {
        $this->massLoadBills($consultations);

        foreach ($consultations as $key => $_consultation) {
            $_consultation->loadRefFacture()->loadRefsReglements();
            if (intval($_consultation->_ref_facture->_du_restant_patient) === 0) {
                unset($consultations[$key]);
            }
        }

        return $consultations;
    }

    /**
     * Count unpaid consultations
     *
     * @throws Exception
     */
    public function sumUnpaid(array $patients_id, array $users = []): array
    {
        $sum_by_patient = [];
        $str_sum        = 'sum';
        $str_reg        = 'sum_reg';
        $str_av         = 'sum_av';

        $where = [
            'plageconsult.chir_id'       => $this->ds->prepareIn($users),
            'consultation.patient_id'    => $this->ds->prepareIn($patients_id),
            'facture_cabinet.du_patient' => '> 0 OR facture_cabinet.du_patient IS NOT NULL',
        ];

        $ljoin = [
            'facture_liaison' => "facture_liaison.object_id = consultation.consultation_id AND facture_liaison.object_class = 'CConsultation'",
            'facture_cabinet' => "facture_cabinet.facture_id = facture_liaison.facture_id",
            'plageconsult'    => "consultation.plageconsult_id = plageconsult.plageconsult_id",
        ];

        $request = new CRequest();
        $request->addSelect('consultation.patient_id')
                ->addColumn("COALESCE(SUM(facture_cabinet.du_patient), 0) as $str_sum")
                ->addTable('consultation')
                ->addLJoin($ljoin)
                ->addWhere($where)
                ->addGroup('consultation.patient_id');

        $res_sum = $this->ds->loadList($request->makeSelect());

        if ($res_sum) {
            $sum_by_patient = array_combine(array_column($res_sum, "patient_id"), $res_sum);

            $request = new CRequest();
            $request->addSelect('consultation.patient_id')
                    ->addColumn("COALESCE(SUM(reglement.montant), 0) as $str_reg")
                    ->addColumn("COALESCE(SUM(facture_avoir.montant), 0) as $str_av")
                    ->addTable('consultation')
                    ->addLJoin(
                        array_merge($ljoin, [
                            'reglement'     => "reglement.object_id = facture_cabinet.facture_id AND reglement.object_class = 'CFactureCabinet'",
                            'facture_avoir' => "facture_avoir.object_id = facture_cabinet.facture_id AND facture_avoir.object_class = 'CFactureCabinet'",
                        ])
                    )
                    ->addWhere($where)
                    ->addGroup('consultation.patient_id');

            $res = $this->ds->loadList($request->makeSelect());

            foreach ($res as $patient) {
                if (isset($sum_by_patient[$patient["patient_id"]])) {
                    $sum_by_patient[$patient["patient_id"]][$str_sum] =
                        round(floatval($sum_by_patient[$patient["patient_id"]][$str_sum]), 2)
                        - (round(floatval($patient[$str_reg]), 2) + round(floatval($patient[$str_av]), 2));
                }
            }
        }

        return $sum_by_patient;
    }

    /**
     * @param CMediusers[]           $users
     * @param DateTimeImmutable|null $date
     * @param string                 $context
     *
     * @return array
     * @throws Exception
     */
    public function getIdsWithMissingPayment(array             $users,
                                             DateTimeImmutable $date = null,
                                             string            $context = "patient"
    ): array {
        $column = "patient_id";

        if ($context === "consultation") {
            $column = "consultation_id";
        }

        $where = [
            'plageconsult.chir_id'       => $this->ds->prepareIn(array_column($users, "_id")),
            'facture_cabinet.du_patient' => '> 0',
        ];

        if ($date) {
            $where['plageconsult.date'] = $this->ds->prepare('= ?', $date->format('Y-m-d'));
        }

        $ljoin = [
            'facture_liaison' => "facture_liaison.object_id = consultation.consultation_id AND facture_liaison.object_class = 'CConsultation'",
            'facture_cabinet' => "facture_cabinet.facture_id = facture_liaison.facture_id",
            'plageconsult'    => "consultation.plageconsult_id = plageconsult.plageconsult_id",
        ];

        // Total sum paid by the patients
        $request_paid = new CRequest();
        $request_paid->addSelect("consultation.$column, SUM(COALESCE(reglement.montant, 0)) as sum_paid")
                     ->addTable('consultation')
                     ->addLJoin(
                         array_merge(
                             $ljoin,
                             ['reglement' => "reglement.object_id = facture_cabinet.facture_id AND reglement.object_class = 'CFactureCabinet'"]
                         )
                     )
                     ->addWhere($where)
                     ->addGroup("consultation.$column");

        $sum_paid            = $this->ds->loadList($request_paid->makeSelect());
        $sum_paid_by_patient = array_combine(array_column($sum_paid, $column), $sum_paid);

        // Total sum due by the patients
        $request = new CRequest();
        $request->addSelect("consultation.$column")
                ->addTable('consultation')
                ->addColumn('SUM(COALESCE(facture_cabinet.du_patient,0))', 'sum_du')
                ->addLJoin($ljoin)
                ->addWhere($where)
                ->addGroup("consultation.$column");

        $sum_du = $this->ds->loadList($request->makeSelect());

        // Only keep the ones that haven't fully paid their bills
        foreach ($sum_du as $sum) {
            if (
                isset($sum_paid_by_patient[$sum[$column]]) &&
                floatval($sum["sum_du"]) <= floatval($sum_paid_by_patient[$sum[$column]]["sum_paid"])
            ) {
                unset($sum_paid_by_patient[$sum[$column]]);
            }
        }

        return array_keys($sum_paid_by_patient);
    }

    public function getListConsultByDateAndPraticianForPatient(CPatient $patient, CMediusers $chirId, $date): array
    {
        $ljoin       = $this->joinShift();
        $where       = $this->addPatientId($patient);
        $where       = $this->addWhereDate($date, $where);
        $where       = $this->addWhereChirId($chirId, $where);
        $this->order = "`consultation`.`heure` ASC";
        $this->limit = "0, 10";

        return $this->object->loadList(
            $where,
            $this->order,
            $this->limit,
            null,
            $ljoin
        );
    }

    /**
     * @param CMediusers[] $users
     *
     * @return CConsultation[]
     * @throws Exception
     */
    public function find(array $users = [], ?DateTimeImmutable $date = null, $where = [], array $ljoin = []): array
    {
        if ($users) {
            $ljoin = $this->joinShift($ljoin);
            $where = $this->addWhereChirIdIn(array_column($users, "_id"), $where);
        }

        if ($date) {
            $ljoin = $this->joinShift($ljoin);
            $where = $this->addWhereDate($date, $where);
        }

        $where = $this->addWhereType('consultation', $where);

        return $this->object->loadList(
            array_merge($this->where, $where),
            $this->order,
            $this->limit,
            null,
            $ljoin
        );
    }

    /**
     * @param array                  $users
     * @param DateTimeImmutable|null $date
     * @param array                  $where
     *
     * @return int
     * @throws Exception
     */
    public function count(array $users = [], ?DateTimeImmutable $date = null, array $where = [], array $ljoin = []): int
    {
        if ($users) {
            $ljoin = $this->joinShift($ljoin);
            $where = $this->addWhereChirIdIn(array_column($users, "_id"), $where);
        }

        if ($date) {
            $ljoin = $this->joinShift($ljoin);
            $where = $this->addWhereDate($date, $where);
        }

        $where = $this->addWhereType('consultation', $where);

        return $this->object->countList(
            array_merge($this->where, $where),
            null,
            $ljoin
        );
    }

    public function addWhereType(string $type, array $where = []): array
    {
        if (!isset($where['consultation.type_consultation'])) {
            $where = array_merge($where, ['consultation.type_consultation' => $this->ds->prepare('= ?', $type)]);
        }

        return $where;
    }

    public function addWhereDuPatientNotNull(array $where = []): array
    {
        if (!isset($where['facture_cabinet.du_patient'])) {
            $where = array_merge(
                $where,
                ['facture_cabinet.du_patient' => '> 0 OR facture_cabinet.du_patient IS NOT NULL']
            );
        }

        return $where;
    }

    public function addWhereCancelled(bool $cancelled = false, array $where = []): array
    {
        return array_merge($where, ["consultation.annule" => $this->ds->prepare('= ?', $cancelled ? '1' : '0')]);
    }

    public function addWhereSettlementNull(array $where = []): array
    {
        return array_merge($where, ['reglement.reglement_id' => "IS NULL"]);
    }

    public function addPatientIdIn(array $patient_ids, array $where = []): array
    {
        return array_merge($where, ['consultation.patient_id' => $this->ds->prepareIn($patient_ids)]);
    }

    public function addConsultationIdIn(array $consultation_ids, array $where = []): array
    {
        return array_merge($where, ['consultation.consultation_id' => $this->ds->prepareIn($consultation_ids)]);
    }

    public function joinReglement(array $ljoin = []): array
    {
        $ljoin              = $this->joinFactureCabinet($ljoin);
        $ljoin['reglement'] = "reglement.object_id = facture_cabinet.facture_id AND reglement.object_class = 'CFactureCabinet'";

        return $ljoin;
    }

    public function joinFactureCabinet(array $ljoin = []): array
    {
        $ljoin['facture_liaison'] = 'facture_liaison.object_id = consultation.consultation_id';
        $ljoin['facture_cabinet'] = 'facture_cabinet.facture_id = facture_liaison.facture_id';

        return $ljoin;
    }

    /**
     * @param CMediusers $user
     * @param array      $where
     *
     * @return array
     */
    private function addWhereChirId(CMediusers $user, array $where = []): array
    {
        if (!isset($where['plageconsult.chir_id'])) {
            $where = array_merge($where, ['plageconsult.chir_id' => $this->ds->prepare('= ?', $user->_id)]);
        }

        return $where;
    }

    /**
     * @param array $users_id
     * @param array $where
     *
     * @return array
     */
    public function addWhereChirIdIn(array $users_id, array $where = []): array
    {
        if (!isset($this->tmp_where['plageconsult.chir_id'])) {
            $where = array_merge($where, ['plageconsult.chir_id' => CSQLDataSource::prepareIn($users_id)]);
        }

        return $where;
    }

    /**
     * @param DateTimeImmutable $date
     * @param array             $where
     *
     * @return array
     */
    public function addWhereDate(DateTimeImmutable $date, array $where = []): array
    {
        return array_merge($where, ['plageconsult.date' => $this->ds->prepare('= ?', $date->format('Y-m-d'))]);
    }

    public function addPatientId(CPatient $patient, array $where = []): array
    {
        return array_merge($where, ['consultation.patient_id' => $this->ds->prepare('= ?', $patient->_id)]);
    }

    private function joinShift(array $ljoin = []): array
    {
        $ljoin['plageconsult'] = 'consultation.plageconsult_id = plageconsult.plageconsult_id';

        return $ljoin;
    }

    protected function getObjectInstance(): CStoredObject
    {
        return new CConsultation();
    }

    protected function massLoadRelation(array $objects, string $relation): void
    {
        switch ($relation) {
            case RequestRelations::QUERY_KEYWORD_ALL:
                $this->massLoadPatient($objects);
                $this->massLoadPlageConsult($objects);
                $this->massLoadBills($objects);
                $this->massLoadFiles($objects);
                break;
            case CConsultation::RELATION_PATIENT:
                $this->massLoadPatient($objects);
                break;
            case CConsultation::RELATION_PLAGE_CONSULT:
                $this->massLoadPlageConsult($objects);
                break;
            case CConsultation::RELATION_FACTURE_CABINET:
                $this->massLoadBills($objects);
                break;
            case CConsultation::RELATION_FILES:
                $this->massLoadFiles($objects);
                break;
            default:
                // Do nothing
        }
    }

    public function massLoadPatient(array $objects): void
    {
        CStoredObject::massLoadFwdRef($objects, 'patient_id');
    }

    public function massLoadPlageConsult(array $objects): void
    {
        CStoredObject::massLoadFwdRef($objects, 'plageconsult_id');
    }

    public function massLoadFiles(array $objects): void
    {
        CStoredObject::massLoadBackRefs($objects, 'files');
    }

    public function massLoadReports(array $objects): void
    {
        CStoredObject::massLoadBackRefs($objects, 'documents');
    }

    public function massLoadPrescriptions(array $objects): void
    {
        CStoredObject::massLoadBackRefs($objects, 'prescriptions');
    }

    public function massLoadBills(array $objects): void
    {
        $joints = CStoredObject::massLoadBackRefs($objects, 'facturable');
        if ($joints) {
            $bills = CStoredObject::massLoadFwdRef($joints, 'facture_id');

            if ($bills) {
                $bills_keys = array_combine(array_column($bills, "_id"), $bills);
                CStoredObject::massLoadBackRefs($bills_keys, 'reglements');
                CStoredObject::massLoadBackRefs($bills_keys, 'avoirs');
            }
        }
    }
}
