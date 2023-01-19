<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use DateTime;
use Ox\Core\CAppUI;
use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\CConsultation;
use Ox\Mediboard\Cabinet\CTarif;
use Ox\Mediboard\Jfse\DataModels\CJfseInvoice;
use Ox\Mediboard\Jfse\DataModels\CJfsePatient;
use Ox\Mediboard\Jfse\Domain\Dictionary\DictionaryService;
use Ox\Mediboard\Jfse\Domain\Invoicing\CommonLawAccident;
use Ox\Mediboard\Jfse\Domain\Invoicing\ComplementaryHealthInsurance;
use Ox\Mediboard\Jfse\Domain\Invoicing\InsuredParticipationAct;
use Ox\Mediboard\Jfse\Domain\Invoicing\InvoicingService;
use Ox\Mediboard\Jfse\Domain\Invoicing\RuleForcing;
use Ox\Mediboard\Jfse\Domain\Invoicing\SecuringModeEnum;
use Ox\Mediboard\Jfse\Domain\PrescribingPhysician\PrescribingPhysicianService;
use Ox\Mediboard\Jfse\Domain\UserManagement\UserManagementService;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvAcquisitionModeEnum;
use Ox\Mediboard\Jfse\Domain\Vital\ApCvService;
use Ox\Mediboard\Jfse\Exceptions\ApiException;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\Utils;
use Ox\Mediboard\Jfse\ViewModels\Invoicing\CComplement;
use Ox\Mediboard\Jfse\ViewModels\Invoicing\CJfseInvoiceView;
use Ox\Mediboard\Jfse\ViewModels\PrescribingPhysician\CJfsePrescription;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Mediboard\Patients\CPatient;
use Ox\Mediboard\Sante400\CIdSante400;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class InvoicingController extends AbstractController
{
    private const LEGACY_INVOICING_NO_USER_ACCOUNT    = 1;
    private const LEGACY_INVOICING_USER_CHOICE      = 2;
    private const LEGACY_INVOICING_USER_CHOICE_FLAG = 'jfse:legacy_invoicing';

    /** @var string[][] */
    public static $routes = [
        "thirdParty/set" => [
            "method" => "setThirdParty",
        ],
        'invoice/create' => [
            'method' => 'createInvoice'
        ],
        "invoice/cancel" => [
            "method" => "cancelInvoice",
        ],
        'common_law_accident/store' => [
            'method' => 'storeCommonLawAccident'
        ],
        'rule/force' => [
            'method' => 'forceRule'
        ],
        "invoice/delete" => [
            "method" => "deleteInvoice",
        ],
        'invoice/selectSecuringMode' => [
            'method' => 'selectInvoiceSecuringMode'
        ],
        'invoice/setApCv' => [
            'method' => 'switchInvoiceToApCv',
        ],
        'invoice/setTreatmentType' => [
            'method' => 'setTreatmentType'
        ],
        'invoice/pav' => [
            'method'  => 'setInsuredParticipation',
        ],
        "invoice/validate" => [
            "method" => "validateInvoice",
        ],
        "invoice/view" => [
            "method" => "viewInvoice",
        ],
        'invoice/anonymize' => [
            'method' => 'setAnonymization'
        ],
        'invoice/messages' => [
            'method' => 'getInvoiceMessages'
        ],
        'childrenConsultation/assistant' => [
            'method' => 'getChildrenConsultationAssistant'
        ],
        'questions/answer' => [
            'method' => 'answerQuestions',
        ],
        'prescription/edit' => [
            'method' => 'editPrescription'
        ],
        'situationCode/select' => [
            'method' => 'selectSituationCode'
        ],
        'legacyInvoicing/enable' => [
            'method'  => 'setLegacyInvoicingFlag',
            'request' => 'legacyInvoicingFlagRequest'
        ],
        'legacyInvoicing/disable' => [
            'method'  => 'deleteLegacyInvoicingFlag',
            'request' => 'legacyInvoicingFlagRequest'
        ],
        'patient/cardlessRequirements' => [
            'method' => 'setPatientCardlessMode'
        ],
        'patient/situationCodesList' => [
            'method' => 'refreshSituationCodes'
        ],
        'patient/autocompleteOrganisms' => [
            'method' => 'autocompleteOrganisms'
        ],
    ];

    public static function getRoutePrefix(): string
    {
        return 'invoicing';
    }

    public function setThirdPartyRequest(): Request
    {
        $data = [
            "invoice_id"      => CView::post("invoice_id", "str"),
            "third_party_amo" => (bool)CView::post("third_party_amo", "bool"),
            "third_party_amc" => (int)CView::post("third_party_amc", "num"),
        ];

        return new Request([], $data);
    }

    /**
     * @route invoicing/thirdParty/set
     */
    public function setThirdParty(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $service = new InvoicingService();
        $complementary = ComplementaryHealthInsurance::hydrate($request->request->all());

        $service->setThirdPartyPayment($request->get('invoice_id'), $complementary);
        return SmartyResponse::message('CJfseInvoice-msg-modified', SmartyResponse::MESSAGE_INFO);
    }

    /**
     * Displays the Invoice view.
     *
     * Depending on the situation, different templates may be displayed
     *
     * @param Request $request
     *
     * @return SmartyResponse
     * @throws \Ox\Core\CMbModelNotFoundException
     */
    public function viewInvoice(Request $request): SmartyResponse
    {
        $consultation = CConsultation::findOrFail($request->get('consultation_id'));
        $invoice_id = $request->get('invoice_id');
        Utils::setJfseUserIdFromConsultation($consultation);

        /* If the user account is not created, we display the legacy view of the facturation */
        if (!UserManagementService::userHasAccount($consultation->loadRefPraticien())) {
            $response = $this->displayLegacyFacturationView($consultation, self::LEGACY_INVOICING_NO_USER_ACCOUNT);
        } elseif (self::checkConsulationLegacyInvoicing($consultation)) {
            /* If the user has manually toggled the legacy facturation view, we display it */
            $response = $this->displayLegacyFacturationView($consultation, self::LEGACY_INVOICING_USER_CHOICE);
        } elseif (
            !InvoicingService::checkInvoiceRequirements($consultation)
            && !InvoicingService::consultationHasInvoices($consultation)
        ) {
            /* If the patient do not exist in JFse, and no invoices are pending, we display a specific view */
            $response = $this->displayInvoiceCreationView($consultation, true);
        } elseif (!InvoicingService::consultationHasInvoices($consultation)) {
            /* If no Invoices were created, we display the creation view */
            $response = $this->displayInvoiceCreationView($consultation);
        } else {
            $service = new InvoicingService();
            try {
                $invoice = CJfseInvoiceView::getFromEntity($service->viewInvoice($consultation, $invoice_id));
                $response = $this->displayInvoiceView($invoice, $consultation);
            } catch (ApiException $exception) {
                $response = $this->handleApiError($exception, $consultation, $invoice_id);
            }
        }

        return $response;
    }

    public function viewInvoiceRequest(): Request
    {
        CCanDo::checkRead();

        $invoice_id = CView::post('invoice_id', 'str');

        return new Request([], [
            'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation notNull'),
            'invoice_id'      => $invoice_id != '' ? $invoice_id : null
        ]);
    }

    /**
     * Checks if the given ApiException is caused by an unknown invoice by Jfse, and returns the accordig SmartyResponse
     * If not, the exception will be thrown, and an error message will be displayed
     *
     * @param ApiException  $exception
     * @param CConsultation $consultation
     * @param string|null   $invoice_id
     *
     * @return SmartyResponse
     *
     * @throws ApiException
     */
    protected function handleApiError(
        ApiException $exception,
        CConsultation $consultation,
        string $invoice_id = null
    ): SmartyResponse {
        /* Checks if the Api error concerns an unknown invoice id */
        if (str_contains($exception->getLocalizedMessage(), InvoicingService::API_ERROR_UNKNOW_INVOICE_TEXT)) {
            $service = new InvoicingService();
            if (!$invoice_id) {
                /* If no invoice_id was given, we get the id of the first pending invoice,
                 * or the last validated invoice */
                $invoice_data_model = InvoicingService::getInvoiceDataModelFromConsultation($consultation);
                $invoice_id = $invoice_data_model->jfse_id;
            }

            if ($invoice_id) {
                $service->deleteInvoiceDataModel($invoice_id);
            }

            if (InvoicingService::consultationHasInvoices($consultation, false)) {
                /* If after the deletion of the invoice data model, the consultation still has invoices,
                 * we display the last one */
                $invoice = CJfseInvoiceView::getFromEntity($service->viewInvoice($consultation));
                $response = $this->displayInvoiceView($invoice, $consultation);
            } else {
                /* Otherwise, we display the creation view */
                $response = $this->displayInvoiceCreationView($consultation);
            }
        } else {
            throw $exception;
        }

        return $response;
    }

    public function validateInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();
        $invoice = $service->getInvoice($request->get('invoice_id'));
        $response = null;
        if ($invoice->isComplementsNeeded() && !$request->get('force_validation')) {
            $complement = $service->getComplements($request->get('invoice_id'));

            if ($complement) {
                /* The invoice is not validated, and the complements view must be displayed */
                $content = new SmartyResponse('invoicing/complements', [
                    'complement' => CComplement::getFromEntity($complement),
                    'invoice_id' => $invoice->getId()
                ]);

                $response = new JsonResponse([
                    'success' => true,
                    'html'    => base64_encode($content->getContent()),
                    'title'   => "CComplement.type.{$complement->getType()}"
                ]);
            }
        }

        if ($invoice->isApCv() && $invoice->isApCvContextExpired()) {
            /* The ApCv context is expired and must be renewed */
            $content = new SmartyResponse('invoicing/expired_apcv_context', [
                'invoice_id' => $invoice->getId()
            ]);

            $response = new JsonResponse([
                'success' => true,
                'html'    => base64_encode($content->getContent()),
                'title'   => "CJfseInvoiceView-action-renew_apcv_context"
            ]);
        }

        if (!$response) {
            $invoice = $service->validateInvoice($request->get('invoice_id'));
            if (!$invoice->getDataModel()->isValidated() && $invoice->getUserInterface()->getDisplayPav()) {
                /* The invoice is not validated, and the PAV view must be displayed */
                $content  = new SmartyResponse('invoicing/insured_participation', [
                    'invoice' => CJfseInvoiceView::getFromEntity($invoice)
                ]);
                $response = new JsonResponse([
                    'success' => true,
                    'html'    => base64_encode($content->getContent()),
                    'title'   => 'CJfseInvoiceView-title-insured_participation'
                ]);
            } else {
                $response = new JsonResponse(['success' => true]);
            }
        }

        return $response;
    }

    public function validateInvoiceRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id'        => CView::post('invoice_id', 'str notNull'),
            'force_validation'  => (bool)CView::post('force_validation', 'bool default|0')
        ]);
    }

    public function createInvoice(Request $request): SmartyResponse
    {
        $consultation = CConsultation::findOrFail($request->get('consultation_id'));
        Utils::setJfseUserIdFromConsultation($consultation);
        $securing_mode = $request->get('securing_mode') ? SecuringModeEnum::from($request->get('securing_mode')) : null;

        $service = new InvoicingService();
        $invoice = CJfseInvoiceView::getFromEntity($service->createInvoice(
            $consultation,
            $securing_mode,
            $request->get('situation_code'),
            $request->get('vitale_nir'),
            $request->get('apcv')
        ));

        return $this->displayInvoiceView($invoice, $consultation);
    }

    public function createInvoiceRequest(): Request
    {
        CCanDo::checkEdit();

        $securing_mode = CView::post('securing_mode', SecuringModeEnum::getProp());
        $situation_code = CView::post('situation_code', 'str maxLength|5');

        return new Request([], [
            'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation notNull'),
            'securing_mode'   => ($securing_mode !== '' && !is_null($securing_mode)) ? (int)$securing_mode : null,
            'situation_code'  => ($situation_code !== '' && !is_null($situation_code)) ? $situation_code : null,
            'vitale_nir'      => CView::post('vitale_nir', 'str'),
            'apcv'            => CView::post('apcv', 'bool default|0') === '1'
        ]);
    }

    public function switchInvoiceToApCv(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $mode = ApCvAcquisitionModeEnum::from($request->get('mode'));
        $vital_card = (new ApCvService())->generateApCvContext($mode, $request->get('qrcode_content'));

        $invoice = CJfseInvoiceView::getFromEntity(
            (new InvoicingService())->switchInvoiceToApCv($request->get('invoice_id'), $vital_card)
        );

        return $this->displayInvoiceView($invoice, $invoice->consultation);
    }

    public function switchInvoiceToApCvRequest(): Request
    {
        CCanDo::checkEdit();

        $securing_mode = CView::post('securing_mode', SecuringModeEnum::getProp());
        $situation_code = CView::post('situation_code', 'str maxLength|5');

        return new Request([], [
            'invoice_id'        => CView::post('invoice_id', 'str notNull'),
            'mode'              => (int)CView::post('mode', ApCvAcquisitionModeEnum::getProp() . ' notNull'),
            'qrcode_content'    => CView::post('qrcode_content', 'str'),
        ]);
    }

    public function cancelInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();
        $result = $service->cancelInvoice($request->get('invoice_id'));

        return new JsonResponse(['success' => $result]);
    }

    public function cancelInvoiceRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id' => CView::post('invoice_id', 'str notNull')
        ]);
    }

    public function deleteInvoice(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();
        $result = $service->deleteInvoice($request->get('invoice_id'));

        return new JsonResponse(['success' => $result]);
    }

    public function deleteInvoiceRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id' => CView::post('invoice_id', 'str notNull')
        ]);
    }

    public function answerQuestions(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();
        $result = $service->answerQuestions($request->get('invoice_id'), $request->get('questions'));

        return new JsonResponse(['success' => $result]);
    }

    public function answerQuestionsRequest(): Request
    {
        $questions_id = CView::post('questions_id', ['str', 'default' => []]);
        $answers = CView::post('answers', ['str', 'default' => []]);
        $natures = CView::post('natures', ['str', 'default' => []]);

        $questions = [];
        foreach ($questions_id as $index => $item) {
            $questions[] = [
                'id' => $item,
                'answer' => $answers[$index],
                'nature' => $natures[$index],
            ];
        }

        return new Request([], [
            'invoice_id' => CView::post('invoice_id', 'str notNull'),
            'questions'  => $questions
        ]);
    }

    public function selectInvoiceSecuringMode(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $service = new InvoicingService();

        $invoice = CJfseInvoiceView::getFromEntity($service->selectSecuringMode(
            $request->get('invoice_id'),
            $request->get('securing_mode'),
            $request->get('situation_code')
        ));
        $invoice->loadActs();
        $invoice->setCarePathDoctors();


        $patient = $invoice->consultation->loadRefPatient();
        $patient_data_model = CJfsePatient::getFromPatient($patient);

        return new SmartyResponse('invoicing/invoice', [
            'consultation'       => $invoice->consultation,
            'tarifs'             => CTarif::loadTarifsUser($invoice->consultation->loadRefPraticien()),
            'patient'            => $patient,
            'patient_data_model' => $patient_data_model,
            'invoice'            => $invoice,
            'exonerations'       => (new InvoicingService())->getExonerationList($invoice->id),
            'invoices'           => InvoicingService::getAllInvoicesFromConsultation($invoice->consultation),
        ]);
    }

    public function selectInvoiceSecuringModeRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id'     => CView::post('invoice_id', 'str notNull'),
            'securing_mode'  => new SecuringModeEnum((int)CView::post('securing_mode', SecuringModeEnum::getProp())),
            'situation_code' => CView::post('situation_code', 'str')
        ]);
    }

    public function editPrescription(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $prescription = CJfsePrescription::getFromEntity(
            (new InvoicingService())->getPrescriptionFromInvoice($request->get('invoice_id'))
        );

        $user = CMediusers::get();

        return new SmartyResponse("prescribing_physician/edit", [
            "prescription" => $prescription,
            "prescribing_physician"         => $prescription->prescriber,
            "specialities"                  =>
                (new PrescribingPhysicianService($user->_guid))->getPhysicianSpecialitiesList(),
            'ajax'                          => true,
            'jfse_user_id'                  => Utils::getJfseUserId(),
        ]);
    }

    public function editPrescriptionRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], ['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function setAnonymization(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $succes = (new InvoicingService())->anonymize($request->get('invoice_id'));
        return new JsonResponse(['success' => $succes]);
    }

    public function setAnonymizationRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], ['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function getInvoiceMessages(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));

        $invoice = CJfseInvoiceView::getFromEntity((new InvoicingService())->getInvoice($request->get('invoice_id')));

        return new SmartyResponse('invoicing/messages', ['invoice' => $invoice]);
    }

    public function getInvoiceMessagesRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], ['invoice_id' => CView::post('invoice_id', 'str notNull')]);
    }

    public function storeCommonLawAccident(Request $request): Response
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $common_law_accident = CommonLawAccident::hydrate(
            [
                "common_law_accident" => (bool) $request->get('common_law_accident'),
                "date"                => ($request->get('accident_date', null))
                    ? new DateTime($request->get("accident_date")) : null,
            ]
        );
        (new InvoicingService())->setCommonLawAccident($common_law_accident, $request->get('invoice_id'));

        return new JsonResponse(['success' => true, 'message' => 'CJfseInvoice-msg-common_law_accident_modified']);
    }

    public function storeCommonLawAccidentRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id'            => CView::post('invoice_id', 'str notNull'),
            'common_law_accident'   => CView::post('common_law_accident', 'bool'),
            'accident_date'         => CView::post('date', ['date', 'default' => null])
        ]);
    }

    public function setTreatmentTypeRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id'     => CView::post('invoice_id', 'str notNull'),
            'treatment_type' => (int)CView::post('treatment_type', 'enum list|0|1|2 notNull'),
        ]);
    }

    public function setTreatmentType(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $service = new InvoicingService();
        $service->setTreatmentType($request->get('invoice_id'), $request->get('treatment_type'));

        return new JsonResponse([
            'success' => true,
            'message' => 'CJfseInvoice-msg-modified'
        ]);
    }

    public function forceRuleRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id'   => CView::post('invoice_id', 'str notNull'),
            'rule_id'      => CView::post('rule_id', 'str notNull'),
            'forcing_type' => CView::post('forcing_type', 'enum list|std|cc default|std')
        ]);
    }

    public function forceRule(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $rule_forcing = RuleForcing::hydrate([
            'serial_id'    => $request->get('rule_id'),
            'forcing_type' => $request->get('forcing_type') === 'std'
                ? RuleForcing::STANDARD_FORCING : RuleForcing::COMPLETE_CONTROL_FORCING,
        ]);

        $service = new InvoicingService();
        $service->forceRule($request->get('invoice_id'), $rule_forcing);

        return new JsonResponse(['success' => true]);
    }

    public function setInsuredParticipationRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'invoice_id'                => CView::post('invoice_id', 'str notNull'),
            'act_index'                 => CView::post('act_index', 'num notNull'),
            'add_insured_participation' => CView::post('add_insured_participation', 'bool'),
            'amo_amount_reduction'      => CView::post('amo_amount_reduction', 'bool'),
        ]);
    }

    public function setInsuredParticipation(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $participation = InsuredParticipationAct::hydrate([
            'index'                     => $request->get('act_index'),
            'add_insured_participation' => (bool)$request->get('add_insured_participation'),
            'amo_amount_reduction'      => (bool)$request->get('amo_amount_reduction'),
        ]);

        $service = new InvoicingService();
        $service->setInsuredParticipation($request->get('invoice_id'), $participation);

        return new JsonResponse(['success' => true]);
    }

    public function getChildrenConsultationAssistantRequest(): Request
    {
        CCanDo::checkEdit();

        $referring_physician = CView::post('referring_physician', 'bool');
        $enforceable_tariff = CView::post('enforceable_tariff', 'bool');

        return new Request([
            'invoice_id'          => CView::post('invoice_id', 'str notNull'),
            'reference_date'      => CView::post('reference_date', 'date notNull'),
            'referring_physician' => $referring_physician != '' ? (bool)$referring_physician : null,
            'enforceable_tariff'  => $enforceable_tariff != '' ? (bool)$enforceable_tariff : null,
        ]);
    }

    public function getChildrenConsultationAssistant(Request $request): JsonResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $service = new InvoicingService();

        $result = $service->getChildrenConsultationAssistant(
            $request->get('invoice_id'),
            $request->get('reference_date'),
            $request->get('enforceable_tariff'),
            $request->get('referring_physician')
        );
        if ($result) {
            $message = 'CJfseChildrenConsultationAssistant-msg-code_added';
        } else {
            $message = 'CJfseChildrenConsultationAssistant-msg-no_code_found';
        }

        return new JsonResponse([
            'success' => $result,
            'message' => $message
        ]);
    }

    protected function displayInvoiceView(CJfseInvoiceView $invoice, CConsultation $consultation): SmartyResponse
    {
        $invoice->loadActs();

        $invoice->setCarePathDoctors();

        $patient = $consultation->loadRefPatient();
        $patient_data_model = CJfsePatient::getFromPatient($patient);

        /* The field du_patient is not automatically set when coding an act.
         * If not set when the cotation is closed, it will remain at 0 */
        if ($consultation->valide !== '1') {
            $consultation->du_patient = $consultation->secteur1 + $consultation->secteur2
                + $consultation->secteur3 + $consultation->du_tva;
        }

        $exonerations = (new InvoicingService())->getExonerationList($invoice->id);

        return new SmartyResponse('invoicing/invoice', [
            'consultation'       => $consultation,
            'tarifs'             => CTarif::loadTarifsUser($consultation->loadRefPraticien()),
            'list_devis'         => $consultation->loadBackRefs('devis_codage', 'creation_date ASC', null, 'devis_codage_id'),
            'patient'            => $patient,
            'patient_data_model' => $patient_data_model,
            'invoice'            => $invoice,
            'exonerations'       => $exonerations,
            'invoices'           => InvoicingService::getAllInvoicesFromConsultation($consultation),
        ]);
    }

    private function displayLegacyFacturationView(
        CConsultation $consultation,
        int $reason = self::LEGACY_INVOICING_NO_USER_ACCOUNT
    ): SmartyResponse {
        $consultation->loadRefPatient();
        $consultation->loadRefPraticien();
        $consultation->loadRefsActes();
        $consultation->loadExtCodesCCAM();
        $consultation->loadRefFacture()->loadRefsReglements();

        $tarifs = [];
        $devis  = [];
        if (!$consultation->valide) {
            $tarifs = CTarif::loadTarifsUser($consultation->_ref_praticien);
            $devis = $consultation->loadBackRefs('devis_codage', 'creation_date ASC', null, 'devis_codage_id');
        }

        $frais_divers  = [];
        if (CAppUI::gconf("dPccam frais_divers use_frais_divers_CConsultation")) {
            $frais_divers = $consultation->loadRefsFraisDivers(count($consultation->_ref_factures) + 1);
            $consultation->loadRefsFraisDivers(null);
        }

        $parameters = [
            'consult'          => $consultation,
            'patient'          => $consultation->_ref_patient,
            'praticien'        => $consultation->_ref_praticien,
            'displayFSE'       => 0,
            'tarifs'           => $tarifs,
            'list_devis'       => $devis,
            'reason'           => $reason,
            'frais_divers'     => $frais_divers,
        ];

        return new SmartyResponse('invoicing/legacy_facturation', $parameters);
    }

    private function displayInvoiceCreationView(
        CConsultation $consultation,
        bool $missing_requirements = false
    ): SmartyResponse {
        return new SmartyResponse('invoicing/invoice_creation', [
            'consultation'  => $consultation,
            'patient'       => $consultation->loadRefPatient(),
            'cardless_mode' => SecuringModeEnum::CARDLESS()->getValue(),
            'degraded_mode' => SecuringModeEnum::DEGRADED()->getValue(),
            'missing_requirements' => $missing_requirements,
        ]);
    }

    public function setPatientCardlessMode(Request $request): SmartyResponse
    {
        if ($request->get('invoice_id')) {
            $invoice_data_model = CJfseInvoice::getFromJfseId($request->get('invoice_id'));
            $consultation = $invoice_data_model->loadConsultation()->loadRefPatient();
        } else {
            $consultation = CConsultation::findOrFail($request->get('consultation_id'));
        }

        Utils::setJfseUserIdFromConsultation($consultation);
        $patient = $consultation->loadRefPatient();

        /* Deletes the value 999 from the centre_gest because it mark the invoice as a demo invoice,
         * which lead to rejects by the CPAM */
        if ($patient->caisse_gest === '999' || $patient->centre_gest === '9999') {
            $patient->caisse_gest = '';
            $patient->centre_gest = '';
            $patient->store();
        }

        $service = new InvoicingService();
        $situations = [];
        if ($patient->code_regime) {
            $situations = $service->getSituationsCodesList($patient);
        }

        $parameters = [
            'consultation_id' => $request->get('consultation_id'),
            'patient'         => $patient,
            'invoice_id'      => $request->get('invoice_id'),
            'securing_mode'   => $request->get('securing_mode'),
            'situations'      => $situations,
        ];

        if (!CJfsePatient::isPatientLinked($patient)) {
            $parameters['set_patient_data'] = true;
            $dictionary = new DictionaryService();
            $parameters['regimes'] = $dictionary->listRegimes();
            $parameters['managing_codes'] = $dictionary->listManagingCodes();
            $parameters['patient_organism'] = $dictionary->getOrganismForPatient($patient);
        } else {
            $parameters['set_patient_data'] = false;
        }

        return new SmartyResponse('invoicing/cardless_mode_patient', $parameters);
    }

    public function setPatientCardlessModeRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([
            'consultation_id' => CView::post('consultation_id', 'ref class|CConsultation'),
            'invoice_id' => CView::post('invoice_id', 'ref class|CJfseInvoice'),
            'securing_mode' => CView::post(
                'securing_mode',
                'enum list|' . SecuringModeEnum::CARDLESS()->getValue()
                . '|' . SecuringModeEnum::DEGRADED()->getValue() . ' notNull'
            ),
        ]);
    }

    public function autocompleteOrganisms(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromMediuser();

        $organisms = [];
        if ($request->get('regime_code')) {
            $organisms = (new DictionaryService())->filterOrganisms(
                $request->get('regime_code'),
                $request->get('organism_label')
            );
        }

        return new SmartyResponse('invoicing/organism_autocomplete', ['organisms' => $organisms]);
    }

    public function autocompleteOrganismsRequest(): Request
    {
        CCanDo::checkRead();

        $label = CView::post('organism_label', 'str');

        return new Request([
            'regime_code' => CView::post('regime_code', 'str maxLength|2'),
            'organism_label' => $label !== '' ? $label : null
        ]);
    }

    public function selectSituationCode(Request $request): SmartyResponse
    {
        Utils::setJfseUserIdFromInvoiceId($request->get('invoice_id'));
        $invoice_data_model = CJfseInvoice::getFromJfseId($request->get('invoice_id'));
        $patient = $invoice_data_model->loadConsultation()->loadRefPatient();

        return new SmartyResponse('invoicing/select_code_situation', [
            'invoice_id'     => $request->get('invoice_id'),
            'securing_mode'  => $request->get('securing_mode'),
            'situations'     => (new InvoicingService())->getSituationsCodesList($patient)
        ]);
    }

    public function selectSituationCodeRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], [
            'invoice_id'     => CView::post('invoice_id', 'str notNull'),
            'securing_mode'  => new SecuringModeEnum((int)CView::post('securing_mode', SecuringModeEnum::getProp()))
        ]);
    }

    public function refreshSituationCodes(Request $request): SmartyResponse
    {
        $patient = new CPatient();
        $patient->code_regime = $request->get('regime_code');

        return new SmartyResponse('invoicing/situation_code_field', [
            'situations' => (new InvoicingService())->getSituationsCodesList($patient),
            'nb_cells' => 0,
        ]);
    }

    public function refreshSituationCodesRequest(): Request
    {
        CCanDo::checkRead();

        return new Request(['regime_code' => CView::post('regime_code', 'str maxLength|2 notNull')]);
    }

    public function setLegacyInvoicingFlag(Request $request): JsonResponse
    {
        $consultation = CConsultation::findOrFail($request->get('consultation_id'));
        $flag = self::getConsulationLegacyInvoicingFlag($consultation);
        $flag->store();

        return new JsonResponse(true);
    }

    public function deleteLegacyInvoicingFlag(Request $request): JsonResponse
    {
        $consultation = CConsultation::findOrFail($request->get('consultation_id'));
        $flag = self::getConsulationLegacyInvoicingFlag($consultation);
        if ($flag->_id) {
            $flag->delete();
        }

        return new JsonResponse(true);
    }

    public function legacyInvoicingFlagRequest(): Request
    {
        CCanDo::checkEdit();

        $consultation_id = CView::post('consultation_id', 'ref class|CConsultation');

        return new Request(['consultation_id' => $consultation_id]);
    }

    /**
     * Checks if the user has manually chosen to use the legacy facturation view, instead of creating a FSE
     * Uses an CIdSante400 object as a flag
     *
     * @param CConsultation $consultation
     *
     * @return bool
     */
    private static function checkConsulationLegacyInvoicing(CConsultation $consultation): bool
    {
        return !is_null(self::getConsulationLegacyInvoicingFlag($consultation)->_id);
    }

    private static function getConsulationLegacyInvoicingFlag(CConsultation $consultation): CIdSante400
    {
        $flag = new CIdSante400();
        $flag->object_class = $consultation->_class;
        $flag->object_id = $consultation->_id;
        $flag->id400 = self::LEGACY_INVOICING_USER_CHOICE_FLAG;
        $flag->loadMatchingObject();

        return $flag;
    }
}
