<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers;

use DateTimeImmutable;
use Exception;
use Ox\Core\Api\Exceptions\ApiException;
use Ox\Core\Api\Request\Content\RequestContentException;
use Ox\Core\Api\Request\RequestApi;
use Ox\Core\Api\Resources\Collection;
use Ox\Core\Api\Resources\Item;
use Ox\Core\CAppUI;
use Ox\Core\CController;
use Ox\Core\CMbArray;
use Ox\Core\CMbDT;
use Ox\Core\CMbException;
use Ox\Core\CMbModelNotFoundException;
use Ox\Core\CStoredObject;
use Ox\Core\Kernel\Exception\HttpException;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\Repositories\ConsultationRepository;
use Ox\Mediboard\CompteRendu\Repositories\CompteRenduRepository;
use Ox\Mediboard\Facturation\CFacture;
use Ox\Mediboard\Facturation\CFactureCabinet;
use Ox\Mediboard\Facturation\Repositories\FactureRepository;
use Ox\Mediboard\Mediusers\CFunctions;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\Repositories\PatientEventRepository;
use Symfony\Component\HttpFoundation\Response;

class CConsultationController extends CController
{
    private const USER_PARAMETER                  = 'user_id';
    private const UNPAID_SUM_PARAMETER            = 'unpaid_sum';
    private const COUNT_DOC_PARAMETER             = 'count_docs';
    private const DATE_PARAMETER                  = 'date';
    private const FILTER_PAYMENT_PARAMETER        = 'filter_payment';
    private const COUNT_STATUSES_PARAMETER        = 'count_statuses';
    private const COUNT_ENVENT_REMINDER_PARAMETER = 'event_reminder_count';
    private const COUNT_UNSIGNED_DOC_PARAMETER    = 'unsigned_doc_count';
    private const COUNT_UNPAID_CONSULT_PARAMETER  = 'unpaid_consult_count';
    private const COUNT_REJECTED_BILL_PARAMETER   = 'rejected_bill_count';

    /**
     * @param RequestApi    $requestApi
     * @param CConsultation $consultation
     *
     * @return Response
     * @throws Exception
     * @api
     */
    public function updateConsultation(RequestApi $requestApi, string $consultation_id): Response
    {
        // todo check permissions
        $consultation = new CConsultation();
        $consultation->load($consultation_id);

        if (!$consultation->_id) {
            throw new HttpException(Response::HTTP_NOT_FOUND, 'Consultation not found');
        }

        $data                  = $requestApi->getContent(true, 'ISO-8859-1');
        $arrivee               = CMbArray::get($data, 'arrivee');
        $consultation->arrivee = $arrivee;

        if ($msg = $consultation->store()) {
            throw new HttpException(Response::HTTP_CONFLICT, $msg);
        }

        $item = Item::createFromRequest($requestApi, $consultation);

        return $this->renderApiResponse($item);
    }

    /**
     * @param RequestApi $request_api
     *
     * @return Response
     * @throws ApiException
     * @throws CMbException
     * @throws RequestContentException
     * @api
     */
    public function createConsultation(RequestApi $request_api): Response
    {
        $immediate = $request_api->getRequest()->get('immediate');
        $user_id   = $request_api->getRequest()->get('user_id');
        $datetime  = $request_api->getRequest()->get('datetime');

        $consultations = $request_api->getModelObjectCollection(
            CConsultation::class,
            ['default', CConsultation::FIELDSET_TYPE, CConsultation::FIELDSET_EXAMEN, CConsultation::FIELDSET_STATUS]
        );


        if ($immediate) {
            $objects = [];
            /** @var CConsultation $consultation */
            foreach ($consultations as $consultation) {
                if ($consultation->type_consultation !== "suivi_patient") {
                    $consultation->motif = CAppUI::tr(
                        CAppUI::gconf('dPcabinet CConsultation default_message_immediate_consult')
                    );
                }
                $consultation->date_at = CMbDT::date($datetime);

                $consultation->createByDatetime(
                    $datetime ?: CMbDT::dateTime(),
                    intval($user_id),
                    $consultation->patient_id,
                    1,
                    $consultation->chrono ?: CConsultation::PLANIFIE,
                    0
                );
                $objects[] = $consultation;
            }

            $collection = new Collection($objects);
        } else {
            $collection = $this->storeCollection($consultations);
        }

        $collection->setModelFieldsets($request_api->getFieldsets());
        $collection->setModelRelations($request_api->getRelations());

        return $this->renderApiResponse($collection, Response::HTTP_CREATED);
    }

    /**
     * @param RequestApi    $request_api
     * @param CConsultation $consultation
     *
     * @return Response
     * @throws ApiException
     * @throws CMbException
     * @throws RequestContentException
     * @api
     */
    public function patchConsultation(RequestApi $request_api, CConsultation $consultation): Response
    {
        /** @var CConsultation $consultation */
        $consultation = $request_api->getModelObject(
            $consultation,
            [CConsultation::FIELDSET_STATUS, CConsultation::FIELDSET_EXAMEN]
        );
        $item         = $this->storeObject($consultation);
        $item->setModelFieldsets(array_merge($request_api->getFieldsets(), [CConsultation::FIELDSET_STATUS]));

        return $this->renderApiResponse($item, Response::HTTP_OK);
    }

    /**
     * @param CConsultation $consultation
     * @param RequestApi    $request_api
     *
     * @return Response
     * @throws ApiException
     * @api
     */
    public function getConsultation(CConsultation $consultation, RequestApi $request_api): Response
    {
        return $this->renderApiResponse(Item::createFromRequest($request_api, $consultation));
    }

    /**
     * @param RequestApi             $request_api
     * @param ConsultationRepository $repository
     *
     * @return Response
     * @throws ApiException
     * @throws CMbModelNotFoundException
     * @throws Exception
     * @see ConsultationRepository::initFromRequest
     * @api
     */
    public function listConsultations(RequestApi $request_api, ConsultationRepository $repository): Response
    {
        $date           = $request_api->getRequest()->get(self::DATE_PARAMETER);
        $user_id        = $request_api->getRequest()->get(self::USER_PARAMETER);
        $sum_unpaid     = $request_api->getRequest()->get(self::UNPAID_SUM_PARAMETER, false);
        $count_docs     = $request_api->getRequest()->get(self::COUNT_DOC_PARAMETER, false);
        $filter_payment = $request_api->getRequest()->get(self::FILTER_PAYMENT_PARAMETER, false);

        $establishment_users = CMediusers::get()->loadProfessionnelDeSante(PERM_READ, CFunctions::getCurrent()->_id);
        $selected_users      = $user_id ? [CMediusers::findOrFail($user_id)] : $establishment_users;
        $date_immutable      = $date ? DateTimeImmutable::createFromFormat("Y-m-d", $date) : null;

        if ($filter_payment === "entirely") {
            // Get consultations that are fully paid
            $consultations = $repository->getFullyPaid($selected_users, $date_immutable);
            $count         = $repository->countFullyPaid($selected_users, $date_immutable);
        } elseif ($filter_payment === "partially") {
            // Get consultations that are partially or not paid
            $consultations = $repository->getPartiallyPaid($selected_users, $date_immutable);
            $count         = $repository->countPartiallyPaid($selected_users, $date_immutable);
        } elseif ($filter_payment === "missing") {
            // Get patients that are missing a payment in their previous consultations
            $consultations = $repository->getPartiallyPaidBasedOnPatientHistory($selected_users, $date_immutable);
            $count         = $repository->countPartiallyPaidBasedOnPatientHistory($selected_users, $date_immutable);
        } // Basic loadlist
        else {
            $consultations = $repository->find($selected_users, $date_immutable);
            $count         = $repository->count($selected_users, $date_immutable);
        }

        $repository->massLoadRelations($consultations, $request_api->getRelations());

        // Load _du_restant_patient if fieldset due is asked
        $fieldset_du_asked = in_array
            (
                CFactureCabinet::RESOURCE_TYPE . '.' . CFacture::FIELDSET_DUE,
                $request_api->getFieldsets()
            )
            && in_array(CConsultation::RELATION_FACTURE_CABINET, $request_api->getRelations());

        if ($fieldset_du_asked) {
            $repository->massLoadBills($consultations);
            foreach ($consultations as $consultation) {
                $consultation->loadRefFacture()->loadRefsReglements();
            }
        }

        $collection = Collection::createFromRequest($request_api, $consultations);

        // Get sum of unpaid consultations from consultation's patients
        if ($sum_unpaid) {
            if (!in_array(CConsultation::RELATION_PLAGE_CONSULT, $request_api->getRelations())) {
                $repository->massLoadPlageConsult($consultations);
            }

            $this->addUnpaidSum($collection, array_column($consultations, 'patient_id'), $selected_users);
        }

        // Get number of doc for each consultation
        if ($count_docs) {
            $prescriptions = CStoredObject::massLoadBackRefs($consultations, 'prescriptions');
            CStoredObject::massLoadBackRefs($prescriptions, 'files');
            $repository->massLoadFiles($consultations);
            $repository->massLoadReports($consultations);

            $this->addCountedDocs($collection);
        }

        // Add to metadata
        $this->addMeta($request_api, $collection, $selected_users, $date_immutable);

        $collection->createLinksPagination(
            $request_api->getOffset(),
            $request_api->getLimit(),
            $count
        );

        return $this->renderApiResponse($collection);
    }

    /**
     * @throws ApiException
     * @throws Exception
     */
    public function addUnpaidSum(Collection $collection, array $patients_id, array $users = []): void
    {
        $sum_by_patients = (new ConsultationRepository())->sumUnpaid($patients_id, array_column($users, "_id"));
        /** @var Item $item */
        foreach ($collection as $item) {
            $item->addAdditionalDatas(
                [
                    'unpaid_consultations_sum' => isset($sum_by_patients[$item->getDatas()->patient_id]) ?
                        $sum_by_patients[$item->getDatas()->patient_id]['sum'] : 0,
                ]
            );
        }
    }


    public function addCountedDocs(Collection $collection): void
    {
        /** @var Item $item */
        foreach ($collection as $item) {
            $item->getDatas()->loadRefsPrescriptions();
            $presc_count = 0;
            if (isset($item->getDatas()->_ref_prescriptions["externe"])) {
                $presc_count = $item->getDatas()->_ref_prescriptions["externe"]->countFiles();
            }
            $item->addAdditionalDatas(
                [
                    'report_count' => $item->getDatas()->countDocs(),
                    'file_count'   => $item->getDatas()->countFiles(),
                    'presc_count'  => $presc_count,
                    'form_count'   => intval($item->getDatas()->countForms()),
                ]
            );
        }
    }

    /**
     * @param RequestApi             $request_api
     * @param Collection             $collection
     * @param CMediusers[]           $users
     * @param DateTimeImmutable|null $date
     *
     * @return void
     * @throws Exception
     */
    private function addMeta(
        RequestApi         $request_api,
        Collection         $collection,
        array              $users = [],
        ?DateTimeImmutable $date = null
    ): void {
        $request              = $request_api->getRequest();
        $count_event_reminder = $request->get(self::COUNT_ENVENT_REMINDER_PARAMETER, false);
        $count_unsigned_doc   = $request->get(self::COUNT_UNSIGNED_DOC_PARAMETER, false);
        $count_unpaid_consult = $request->get(self::COUNT_UNPAID_CONSULT_PARAMETER, false);
        $count_rejected_bill  = $request->get(self::COUNT_REJECTED_BILL_PARAMETER, false);
        $count_statuses       = $request->get(self::COUNT_STATUSES_PARAMETER, false);

        $date_min = DateTimeImmutable::createFromFormat('Y-m-d', CMbDT::date('this week'))->setTime(0, 0);
        $date_max = DateTimeImmutable::createFromFormat('Y-m-d', CMbDT::date('next week'))->setTime(23, 59, 59);

        if ($count_event_reminder) {
            $collection->addMeta(
                "eventReminderCount",
                (new PatientEventRepository())->betweenDates($date_min, $date_max)->countReminder($users)
            );
        }
        if ($count_unsigned_doc) {
            $collection->addMeta("unsignedDocCount", (new CompteRenduRepository())->countUnsigned($users));
        }
        if ($count_unpaid_consult) {
            $collection->addMeta("unpaidConsultCount", (new ConsultationRepository())->countUnpaid($users));
        }
        if ($count_rejected_bill) {
            $collection->addMeta("rejectedBillCount", (new FactureRepository())->countRejectedByNoemie($users));
        }

        if ($count_statuses) {
            $this->addCountedStatus($collection, $users, $date);
        }
    }

    /**
     * @throws Exception
     */
    public function addCountedStatus(
        Collection             $collection,
        array $users = [],
        ?DateTimeImmutable     $date = null
    ): void {
        $repo = new ConsultationRepository();
        $collection->addMeta("receivedCount", $repo->countFinished($users, $date));
        $collection->addMeta("cancelledCount", $repo->countCancelled($users, $date));
        $collection->addMeta("firstCount", $repo->countPremiere($users, $date));
        $collection->addMeta("allCount", $repo->count($users, $date));
    }
}
